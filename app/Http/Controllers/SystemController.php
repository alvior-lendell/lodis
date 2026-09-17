<?php

namespace App\Http\Controllers;

use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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

        return view('systems.index', compact('systems'));
    }

    /**
     * Show the form for creating a new system entry.
     */
    public function create()
    {
        $nextSortOrder = (System::max('default_sort_order') ?? 0) + 1;

        return view('systems.create', compact('nextSortOrder'));
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
            'code'               => 'required|string|max:50|unique:systems,code',
            'url'                => 'required|url|max:255',
            'description'        => 'nullable|string',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_active'          => 'nullable|boolean',
            'default_sort_order' => 'required|integer|min:0',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

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
        return view('systems.edit', compact('system'));
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
            'code'               => 'required|string|max:50|unique:systems,code,' . $system->id,
            'url'                => 'required|url|max:255',
            'description'        => 'nullable|string',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_active'          => 'nullable|boolean',
            'default_sort_order' => 'required|integer|min:0',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

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