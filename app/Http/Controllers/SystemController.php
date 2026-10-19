<?php

namespace App\Http\Controllers;

use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SystemController extends Controller
{
    /**
     * Display a listing of registered systems.
     */
    public function index()
    {
        $systems = System::orderBy('default_sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $systemsFormatted = $systems->map(function ($system) {
            $logoPath = $system->logo ? (str_starts_with($system->logo, 'images/') ? $system->logo : 'images/systems/' . $system->logo) : null;

            return [
                'id'                 => $system->id,
                'slug'               => $system->slug,
                'name'               => $system->name,
                'url'                => $system->url,
                'ping_url'           => $system->ping_url,
                'description'        => $system->description,
                'brand_color'        => $system->brand_color,
                'brand_hover_color'  => $system->brand_hover_color,
                'bg_gradient'        => $system->bg_gradient,
                'badge'              => $system->badge,
                'tag'                => $system->tag,
                'features'           => $system->features,
                'logo_url'           => ($logoPath && file_exists(public_path($logoPath))) ? asset($logoPath) : null,
                'is_active'          => (bool) $system->is_active,
                'is_online'          => (bool) $system->is_online,
                'default_sort_order' => $system->default_sort_order,
                'edit_url'           => route('systems.edit', $system),
                'destroy_url'        => route('systems.destroy', $system),
            ];
        })->values();

        $routes = [
            'index'   => route('systems.index'),
            'create'  => route('systems.create'),
            'store'   => route('systems.store'),
            'reorder' => route('systems.index'),
        ];

        return view('systems.index', compact('systems', 'systemsFormatted', 'routes'));
    }

    /**
     * Show the form for creating a new system entry.
     */
    public function create()
    {
        $nextSortOrder = (System::max('default_sort_order') ?? 0) + 1;
        $oldValues = session()->getOldInput();

        $routes = [
            'index'  => route('systems.index'),
            'store'  => route('systems.store'),
            'cancel' => route('systems.index'),
        ];

        return view('systems.create', compact('nextSortOrder', 'oldValues', 'routes'));
    }

    /**
     * Store a newly created system in storage.
     */
    public function store(Request $request)
    {
        if ($request->has('url')) {
            $cleanUrl = preg_replace('/^https?:\/\//i', '', trim($request->input('url')));
            $request->merge(['url' => 'https://' . ltrim($cleanUrl, '/')]);
        }

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'slug'               => 'nullable|string|max:255|unique:systems,slug',
            'url'                => 'required|url|max:255',
            'ping_url'           => 'nullable|url|max:255',
            'description'        => 'nullable|string',
            'brand_color'        => 'nullable|string|max:255',
            'brand_hover_color'  => 'nullable|string|max:255',
            'bg_gradient'        => 'nullable|string|max:255',
            'badge'              => 'nullable|string|max:255',
            'tag'                => 'nullable|string|max:255',
            'features'           => 'nullable',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_active'          => 'nullable|boolean',
            'default_sort_order' => 'required|integer|min:0',
        ]);

        $validated['slug'] = $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['brand_color'] = $validated['brand_color'] ?? '#00687A';
        $validated['brand_hover_color'] = $validated['brand_hover_color'] ?? '#005261';
        $validated['bg_gradient'] = $validated['bg_gradient'] ?? 'linear-gradient(135deg, #020617 0%, #0f172a 50%, #003843 100%)';

        if (is_array($validated['features'] ?? null)) {
            $validated['features'] = json_encode($validated['features']);
        }

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '_', $image->getClientOriginalName()));
            if (!str_ends_with(strtolower($filename), '.' . $image->getClientOriginalExtension())) {
                $filename .= '.' . $image->getClientOriginalExtension();
            }

            $destinationPath = public_path('images/systems');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $filename);
            $validated['logo'] = $filename;
        }

        System::create($validated);

        return redirect()->route('systems.index')->with('status', 'System platform registered successfully.');
    }

    /**
     * Show the form for editing the specified system.
     */
    public function edit(System $system)
    {
        $logoPath = $system->logo ? (str_starts_with($system->logo, 'images/') ? $system->logo : 'images/systems/' . $system->logo) : null;
        $hasLogo = $logoPath && file_exists(public_path($logoPath));

        $systemFormatted = [
            'id'                 => $system->id,
            'slug'               => old('slug', $system->slug),
            'name'               => old('name', $system->name),
            'url'                => old('url', $system->url),
            'ping_url'           => old('ping_url', $system->ping_url),
            'description'        => old('description', $system->description),
            'brand_color'        => old('brand_color', $system->brand_color),
            'brand_hover_color'  => old('brand_hover_color', $system->brand_hover_color),
            'bg_gradient'        => old('bg_gradient', $system->bg_gradient),
            'badge'              => old('badge', $system->badge),
            'tag'                => old('tag', $system->tag),
            'features'           => old('features', $system->features),
            'default_sort_order' => old('default_sort_order', $system->default_sort_order),
            'is_active'          => (bool) old('is_active', $system->is_active),
            'logo_url'           => $hasLogo ? asset($logoPath) : null,
        ];

        $routes = [
            'update' => route('systems.update', $system),
            'cancel' => route('systems.index'),
        ];

        return view('systems.edit', compact('system', 'systemFormatted', 'routes'));
    }

    /**
     * Update the specified system in storage.
     */
    public function update(Request $request, System $system)
    {
        if ($request->has('url')) {
            $cleanUrl = preg_replace('/^https?:\/\//i', '', trim($request->input('url')));
            $request->merge(['url' => 'https://' . ltrim($cleanUrl, '/')]);
        }

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'slug'               => 'nullable|string|max:255|unique:systems,slug,' . $system->id,
            'url'                => 'required|url|max:255',
            'ping_url'           => 'nullable|url|max:255',
            'description'        => 'nullable|string',
            'brand_color'        => 'nullable|string|max:255',
            'brand_hover_color'  => 'nullable|string|max:255',
            'bg_gradient'        => 'nullable|string|max:255',
            'badge'              => 'nullable|string|max:255',
            'tag'                => 'nullable|string|max:255',
            'features'           => 'nullable',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_active'          => 'nullable|boolean',
            'default_sort_order' => 'required|integer|min:0',
        ]);

        $validated['slug'] = $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        if (is_array($validated['features'] ?? null)) {
            $validated['features'] = json_encode($validated['features']);
        }

        if ($request->hasFile('logo')) {
            if ($system->logo) {
                $oldPath = public_path(str_starts_with($system->logo, 'images/') ? $system->logo : 'images/systems/' . $system->logo);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $image = $request->file('logo');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '_', $image->getClientOriginalName()));
            if (!str_ends_with(strtolower($filename), '.' . $image->getClientOriginalExtension())) {
                $filename .= '.' . $image->getClientOriginalExtension();
            }

            $destinationPath = public_path('images/systems');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $filename);
            $validated['logo'] = $filename;
        }

        $system->update($validated);

        return redirect()->route('systems.index')->with('status', 'System platform configuration updated.');
    }

    /**
     * Remove the specified system from storage.
     */
    public function destroy(System $system)
    {
        if ($system->logo) {
            $logoPath = public_path(str_starts_with($system->logo, 'images/') ? $system->logo : 'images/systems/' . $system->logo);
            if (File::exists($logoPath)) {
                File::delete($logoPath);
            }
        }

        $system->delete();

        return redirect()->route('systems.index')->with('status', 'System platform entry removed.');
    }
}