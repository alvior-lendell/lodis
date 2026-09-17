<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class HolidayController extends Controller
{
    /**
     * Display the holiday management grid and calendar views.
     */
    public function index(Request $request): View
    {
        $selectedYear = (int) $request->get('year', date('Y'));

        $holidays = Holiday::whereYear('date', $selectedYear)
            ->orderBy('date', 'asc')
            ->get();

        return view('holidays.index', compact('holidays', 'selectedYear'));
    }

    /**
     * Synchronize official PH national holidays via MCP Session Handshake Protocol.
     */
    public function sync(Request $request): RedirectResponse
    {
        $year = (int) $request->input('year', date('Y'));
        $mcpUrl = 'https://ph-holidays.godmode.ph/mcp';
        $baseHeaders = [
            'User-Agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) LODISv2-HolidaySync/1.0',
            'Accept'       => 'application/json, text/event-stream',
            'Content-Type' => 'application/json',
        ];

        try {
            // Step 1: Perform MCP Session Handshake
            $initResponse = Http::timeout(15)
                ->withHeaders($baseHeaders)
                ->post($mcpUrl, [
                    'jsonrpc' => '2.0',
                    'id'      => 1,
                    'method'  => 'initialize',
                    'params'  => [
                        'protocolVersion' => '2024-11-05',
                        'capabilities'    => (object) [],
                        'clientInfo'      => [
                            'name'    => 'LODISv2',
                            'version' => '1.0.0',
                        ],
                    ],
                ]);

            if ($initResponse->failed()) {
                Log::error("MCP Init Failed [{$initResponse->status()}]", [
                    'response' => $initResponse->body()
                ]);
                return back()->withErrors(['sync' => "Failed to initialize session with MCP endpoint (HTTP {$initResponse->status()})."]);
            }

            $rawSessionId = $initResponse->header('mcp-session-id') 
                ?? $initResponse->header('Mcp-Session-Id');

            if (!$rawSessionId) {
                Log::error('MCP Handshake failed: Server did not return Mcp-Session-Id header.', [
                    'headers' => $initResponse->headers()
                ]);
                return back()->withErrors(['sync' => 'MCP protocol error: No Mcp-Session-Id header returned during handshake.']);
            }

            $sessionId = trim($rawSessionId);
            $sessionHeaders = array_merge($baseHeaders, ['Mcp-Session-Id' => $sessionId]);

            // Step 2: Send mandatory initialized notification signal
            Http::timeout(10)
                ->withHeaders($sessionHeaders)
                ->post($mcpUrl, [
                    'jsonrpc' => '2.0',
                    'method'  => 'notifications/initialized',
                ]);

            // Step 3: Invoke get_holidays tool with valid Session ID
            $toolResponse = Http::timeout(15)
                ->withHeaders($sessionHeaders)
                ->post($mcpUrl, [
                    'jsonrpc' => '2.0',
                    'id'      => 2,
                    'method'  => 'tools/call',
                    'params'  => [
                        'name'      => 'get_holidays',
                        'arguments' => ['year' => $year],
                    ],
                ]);

            if ($toolResponse->failed()) {
                Log::error("MCP Tool Call Error [{$toolResponse->status()}]", [
                    'response' => $toolResponse->body()
                ]);
                return back()->withErrors(['sync' => "MCP tool execution failed (HTTP {$toolResponse->status()})."]);
            }

            // Parse response handling potential SSE (text/event-stream) formatting
            $json = $this->parseMcpResponse($toolResponse->body());

            if (!$json || !isset($json['result'])) {
                Log::error("MCP Invalid Response Structure [{$toolResponse->status()}]", [
                    'raw_body' => $toolResponse->body()
                ]);
                return back()->withErrors(['sync' => "Failed to parse JSON-RPC response from MCP endpoint."]);
            }

            $rawContent = $json['result']['content'][0]['text'] ?? null;
            $payload = is_string($rawContent) ? json_decode($rawContent, true) : ($json['data'] ?? []);
            
            $mcpHolidays = $payload['data'] 
                ?? $payload['holidays'] 
                ?? (is_array($payload) && isset($payload[0]) ? $payload : []);

            if (empty($mcpHolidays)) {
                return back()->withErrors(['sync' => "No holiday dataset returned by MCP endpoint for year {$year}."]);
            }

            $syncedCount = 0;

            DB::transaction(function () use ($mcpHolidays, &$syncedCount) {
                foreach ($mcpHolidays as $item) {
                    $longWeekend = $item['long_weekend'] ?? [];

                    Holiday::updateOrCreate(
                        ['date' => $item['date']],
                        [
                            'name'                    => $item['name'] ?? 'National Holiday',
                            'type'                    => $item['type'] ?? 'regular',
                            'day_of_week'             => $item['day_of_week'] ?? null,
                            'movable'                 => (bool) ($item['movable'] ?? false),
                            'double_holiday'          => (bool) ($item['double_holiday'] ?? false),
                            'double_holiday_names'    => $item['double_holiday_names'] ?? null,
                            'eid_confirmed'           => isset($item['eid_confirmed']) ? (bool) $item['eid_confirmed'] : null,
                            'estimated_date'          => $item['estimated_date'] ?? null,
                            'confirmed_date'          => $item['confirmed_date'] ?? null,
                            'proclamation_ref'        => $item['proclamation_ref'] ?? ($item['source']['proclamation'] ?? null),
                            'is_part_of_long_weekend' => (bool) ($longWeekend['is_part_of'] ?? false),
                            'long_weekend_details'    => $longWeekend,
                            'source_info'             => $item['source'] ?? null,
                            'notes'                   => $item['notes'] ?? null,
                            'is_active'               => true,
                        ]
                    );
                    $syncedCount++;
                }
            });

            return back()->with('status', "Successfully synchronized {$syncedCount} official PH holidays for {$year}.");
        } catch (\Exception $e) {
            Log::error('PH Holidays MCP Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['sync' => 'MCP Connection Exception: ' . $e->getMessage()]);
        }
    }

    /**
     * Parse JSON-RPC response payload handling both plain JSON and SSE (Server-Sent Events) stream lines.
     */
    private function parseMcpResponse(string $rawBody): ?array
    {
        $rawBody = trim($rawBody);
        if (empty($rawBody)) {
            return null;
        }

        // Direct JSON decode attempt
        $decoded = json_decode($rawBody, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Fallback: Parse SSE (Server-Sent Events) formatted stream lines
        $lines = explode("\n", $rawBody);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, 'data:')) {
                $jsonStr = trim(substr($line, 5));
                $decoded = json_decode($jsonStr, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return null;
    }

    /**
     * Store a custom holiday record or company override.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'date'             => ['required', 'date'],
            'type'             => ['required', 'in:regular,special_non_working,special_working,islamic'],
            'proclamation_ref' => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['day_of_week'] = \Carbon\Carbon::parse($validated['date'])->format('l');

        Holiday::create($validated);

        return back()->with('status', "Holiday '{$validated['name']}' created successfully.");
    }

    /**
     * Remove the specified holiday record.
     */
    public function destroy(Holiday $holiday): RedirectResponse
    {
        $name = $holiday->name;
        $holiday->delete();

        return back()->with('status', "Holiday '{$name}' removed successfully.");
    }
}