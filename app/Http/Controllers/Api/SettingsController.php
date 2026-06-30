<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function show()
    {
        return response()->json(Setting::instance());
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'elearning_url' => 'nullable|url|max:255',
        ]);

        $settings = Setting::instance();

        if ($request->hasFile('school_logo')) {
            if ($settings->school_logo && Storage::disk('public')->exists($settings->school_logo)) {
                Storage::disk('public')->delete($settings->school_logo);
            }
            $validated['school_logo'] = $request->file('school_logo')->store('school', 'public');
        }

        if ($request->input('remove_logo')) {
            if ($settings->school_logo && Storage::disk('public')->exists($settings->school_logo)) {
                Storage::disk('public')->delete($settings->school_logo);
            }
            $validated['school_logo'] = null;
        }

        $settings->update($validated);

        return response()->json([
            'message' => 'تم حفظ الإعدادات',
            'settings' => $settings,
        ]);
    }
}
