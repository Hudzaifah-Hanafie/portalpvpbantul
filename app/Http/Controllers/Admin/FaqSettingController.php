<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqSetting;
use Illuminate\Http\Request;

class FaqSettingController extends Controller
{
    public function edit()
    {
        $setting = FaqSetting::first() ?? new FaqSetting();
        return view('admin.faq.settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'hero_button_text' => 'nullable|string|max:120',
            'hero_button_link' => 'nullable|string|max:255',
            'hero_image' => 'nullable|image|max:2048',
            'intro_title' => 'nullable|string|max:255',
            'intro_description' => 'nullable|string',
            'info_title' => 'nullable|string|max:255',
            'info_description' => 'nullable|string',
            'info_stat_primary_label' => 'nullable|string|max:120',
            'info_stat_primary_value' => 'nullable|string|max:120',
            'info_stat_secondary_label' => 'nullable|string|max:120',
            'info_stat_secondary_value' => 'nullable|string|max:120',
            'contact_title' => 'nullable|string|max:255',
            'contact_description' => 'nullable|string',
            'contact_button_text' => 'nullable|string|max:120',
            'contact_button_link' => 'nullable|string|max:255',
        ]);

        $setting = FaqSetting::first() ?? new FaqSetting();

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = '/storage/' . $request->file('hero_image')->store('faq', 'public');
        }

        $setting->fill($data)->save();

        return redirect()->back()->with('success', 'Pengaturan FAQ diperbarui.');
    }
}
