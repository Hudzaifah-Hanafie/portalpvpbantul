<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpidSetting;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class PpidSettingController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function edit()
    {
        $setting = PpidSetting::first() ?? new PpidSetting();
        return view('admin.ppid.settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'hero_button_text' => 'nullable|string|max:120',
            'hero_button_link' => 'nullable|string|max:255',
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'profile_title' => 'nullable|string|max:255',
            'profile_description' => 'nullable|string',
            'form_title' => 'nullable|string|max:255',
            'form_description' => 'nullable|string',
            'form_embed' => 'nullable|string',
        ]);

        $setting = PpidSetting::first() ?? new PpidSetting();

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = '/storage/' . $request->file('hero_image')->store('ppid', 'public');
        }

        $setting->fill($data)->save();

        $this->logger->log(
            $request->user(),
            'ppid_setting.updated',
            'Pengaturan PPID diperbarui',
            $setting
        );

        return redirect()->back()->with('success', 'Pengaturan PPID diperbarui.');
    }
}
