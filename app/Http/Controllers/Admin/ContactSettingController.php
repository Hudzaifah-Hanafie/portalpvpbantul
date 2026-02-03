<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use Illuminate\Http\Request;

class ContactSettingController extends Controller
{
    public function edit()
    {
        $setting = ContactSetting::first() ?? new ContactSetting();
        return view('admin.contact.settings', compact('setting'));
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
            'map_title' => 'nullable|string|max:255',
            'map_description' => 'nullable|string',
            'map_embed' => 'nullable|string',
            'info_section_title' => 'nullable|string|max:255',
            'info_section_description' => 'nullable|string',
            'cta_title' => 'nullable|string|max:255',
            'cta_description' => 'nullable|string',
            'cta_primary_text' => 'nullable|string|max:120',
            'cta_primary_link' => 'nullable|string|max:255',
            'cta_secondary_text' => 'nullable|string|max:120',
            'cta_secondary_link' => 'nullable|string|max:255',
        ]);

        $setting = ContactSetting::first() ?? new ContactSetting();

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = '/storage/' . $request->file('hero_image')->store('contact', 'public');
        }

        $setting->fill($data)->save();

        return redirect()->back()->with('success', 'Pengaturan hubungi kami diperbarui.');
    }
}
