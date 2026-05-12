<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicServiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class PublicServiceSettingController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function edit()
    {
        $setting = PublicServiceSetting::first() ?? new PublicServiceSetting();
        return view('admin.pelayanan.settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'hero_button_text' => 'nullable|string|max:150',
            'hero_button_link' => 'nullable|string|max:255',
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'intro_title' => 'nullable|string|max:255',
            'intro_description' => 'nullable|string',
            'intro_content' => 'nullable|string',
            'regulation_title' => 'nullable|string|max:255',
            'regulation_items' => 'nullable|string',
            'policy_title' => 'nullable|string|max:255',
            'policy_subtitle' => 'nullable|string|max:255',
            'policy_description' => 'nullable|string',
            'policy_signature' => 'nullable|string|max:255',
            'policy_position' => 'nullable|string|max:255',
            'policy_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'standard_title' => 'nullable|string|max:255',
            'standard_description' => 'nullable|string',
            'standard_document_title' => 'nullable|string|max:255',
            'standard_document_description' => 'nullable|string',
            'standard_document_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:4096',
            'standard_document_badge' => 'nullable|string|max:100',
            'flow_section_title' => 'nullable|string|max:255',
            'flow_section_description' => 'nullable|string',
            'cta_title' => 'nullable|string|max:255',
            'cta_description' => 'nullable|string',
            'cta_primary_text' => 'nullable|string|max:100',
            'cta_primary_link' => 'nullable|string|max:255',
            'cta_secondary_text' => 'nullable|string|max:100',
            'cta_secondary_link' => 'nullable|string|max:255',
        ]);

        $setting = PublicServiceSetting::first() ?? new PublicServiceSetting();

        if ($request->hasFile('hero_image')) {
            if ($setting->hero_image) {
                $old = str_replace('/storage/', '', $setting->hero_image);
                Storage::disk('public')->delete($old);
            }
            $data['hero_image'] = '/storage/' . $request->file('hero_image')->store('pelayanan', 'public');
        }

        if ($request->hasFile('policy_image')) {
            if ($setting->policy_image) {
                $old = str_replace('/storage/', '', $setting->policy_image);
                Storage::disk('public')->delete($old);
            }
            $data['policy_image'] = '/storage/' . $request->file('policy_image')->store('pelayanan', 'public');
        }

        if ($request->hasFile('standard_document_file')) {
            if ($setting->standard_document_file) {
                $old = str_replace('/storage/', '', $setting->standard_document_file);
                Storage::disk('public')->delete($old);
            }
            $data['standard_document_file'] = '/storage/' . $request->file('standard_document_file')->store('pelayanan', 'public');
        }

        $sanitizeRich = fn($value) => $value ? HtmlSanitizer::clean($value) : null;
        $sanitizePlain = fn($value) => $value ? strip_tags($value) : null;

        foreach ([
            'hero_title', 'hero_subtitle', 'hero_button_text', 'hero_button_link',
            'intro_title', 'regulation_title', 'policy_title', 'policy_subtitle',
            'policy_signature', 'policy_position', 'standard_title', 'standard_document_title',
            'standard_document_badge', 'flow_section_title', 'cta_title',
            'cta_primary_text', 'cta_primary_link', 'cta_secondary_text', 'cta_secondary_link',
        ] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = $sanitizePlain($data[$key]);
            }
        }

        foreach ([
            'hero_description', 'intro_description', 'intro_content', 'regulation_items',
            'policy_description', 'standard_description', 'standard_document_description',
            'flow_section_description', 'cta_description',
        ] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = $sanitizeRich($data[$key]);
            }
        }

        $setting->fill($data)->save();

        $this->logger->log(
            $request->user(),
            'pelayanan.setting.updated',
            'Pengaturan pelayanan publik diperbarui',
            $setting
        );

        return redirect()->back()->with('success', 'Pengaturan pelayanan publik diperbarui.');
    }
}
