<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\FaqSetting;
use App\Models\PpidSetting;
use App\Models\PublicationSetting;
use App\Models\PublicServiceSetting;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\HtmlSanitizer;

class SiteSettingController extends Controller
{
    public function edit()
    {
        $settings = SiteSetting::pluck('value', 'key')->toArray();
        return view('admin.settings.site', compact('settings'));
    }

    public function portal()
    {
        $settings = SiteSetting::pluck('value', 'key')->toArray();
        $contactSetting = ContactSetting::first() ?? new ContactSetting();
        $faqSetting = FaqSetting::first() ?? new FaqSetting();
        $publicationSetting = PublicationSetting::first() ?? new PublicationSetting();
        $publicServiceSetting = PublicServiceSetting::first() ?? new PublicServiceSetting();
        $ppidSetting = PpidSetting::first() ?? new PpidSetting();

        return view('admin.settings.portal', compact(
            'settings',
            'contactSetting',
            'faqSetting',
            'publicationSetting',
            'publicServiceSetting',
            'ppidSetting'
        ));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            // Hero & beranda
            'home_hero_title' => 'nullable|string',
            'home_hero_subtitle' => 'nullable|string',
            'home_hero_image' => 'nullable|string',
            'home_hero_cta_primary_text' => 'nullable|string',
            'home_hero_cta_primary_link' => 'nullable|string',
            'home_hero_cta_secondary_text' => 'nullable|string',
            'home_hero_cta_secondary_link' => 'nullable|string',
            'home_benefit_title' => 'nullable|string',
            'home_benefit_image' => 'nullable|string',
            'home_program_title' => 'nullable|string',
            'home_program_subtitle' => 'nullable|string',
            'home_why_title' => 'nullable|string',
            'home_why_image' => 'nullable|string',
            'home_flow_title' => 'nullable|string',
            'home_flow_image' => 'nullable|string',
            'home_news_title' => 'nullable|string',
            'home_news_subtitle' => 'nullable|string',
            'home_testimonial_title' => 'nullable|string',
            'home_testimonial_subtitle' => 'nullable|string',
            'home_partner_title' => 'nullable|string',
            'home_partner_subtitle' => 'nullable|string',
            'home_instructor_title' => 'nullable|string',
            'home_instructor_subtitle' => 'nullable|string',
            'home_gallery_title' => 'nullable|string',
            'home_gallery_subtitle' => 'nullable|string',
            'home_hero_image_upload' => 'nullable|image|max:2048',
            'home_benefit_image_upload' => 'nullable|image|max:2048',
            'home_why_image_upload' => 'nullable|image|max:2048',
            'home_flow_image_upload' => 'nullable|image|max:2048',

            // SIAP Kerja / Skillhub API & SSO
            'siapkerja_client_id' => 'nullable|string',
            'siapkerja_client_secret' => 'nullable|string',
            'siapkerja_redirect' => 'nullable|string',
            'siapkerja_scope' => 'nullable|string',
            'siapkerja_api_base' => 'nullable|string',
            'siapkerja_service_base' => 'nullable|string',
            'siapkerja_service_token' => 'nullable|string',
            'siapkerja_service_program_id' => 'nullable|string',
            'siapkerja_service_schedule_id' => 'nullable|string',
            'siapkerja_service_instructor_id' => 'nullable|string',
            'siapkerja_service_participant_id' => 'nullable|string',
            'siapkerja_service_pencaker_id' => 'nullable|string',
            'siapkerja_service_city_code' => 'nullable|string',
            'siapkerja_service_prov_code' => 'nullable|string',
            'siapkerja_service_vocational' => 'nullable|string',
            'siapkerja_service_sub_vocational' => 'nullable|string',
            'siapkerja_profile_api_base' => 'nullable|string',
            'siapkerja_token_url' => 'nullable|string',
            'siapkerja_profile_url' => 'nullable|string',

            // Email delivery
            'mail_mailer' => 'nullable|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:tls,ssl,none',
            'mail_from_address' => 'nullable|string',
            'mail_from_name' => 'nullable|string',
            'email_confirmations_enabled' => 'nullable|boolean',
            'email_notifications_enabled' => 'nullable|boolean',

            'cta_title' => 'nullable|string',
            'cta_subtitle' => 'nullable|string',
            'cta_button_1_text' => 'nullable|string',
            'cta_button_1_link' => 'nullable|string',
            'cta_button_2_text' => 'nullable|string',
            'cta_button_2_link' => 'nullable|string',
            'footer_address' => 'nullable|string',
            'footer_email' => 'nullable|string',
            'footer_phone' => 'nullable|string',
            'footer_phone_alt' => 'nullable|string',
            'footer_instagram' => 'nullable|string',
            'footer_facebook' => 'nullable|string',
            'footer_twitter' => 'nullable|string',
            'footer_youtube' => 'nullable|string',
            'footer_sp4n' => 'nullable|string',
            'footer_operasional' => 'nullable|string',
            'footer_embed_map' => 'nullable|string',
        ]);

        // Handle image uploads and override corresponding URL fields
        $uploadFields = [
            'home_hero_image_upload' => 'home_hero_image',
            'home_benefit_image_upload' => 'home_benefit_image',
            'home_why_image_upload' => 'home_why_image',
            'home_flow_image_upload' => 'home_flow_image',
        ];

        foreach ($uploadFields as $uploadKey => $settingKey) {
            if ($request->hasFile($uploadKey)) {
                $path = $request->file($uploadKey)->store('site-settings', 'public');
                $data[$settingKey] = Storage::url($path);
            }
            unset($data[$uploadKey]);
        }

        if ($request->has('email_confirmations_enabled')) {
            $data['email_confirmations_enabled'] = $request->boolean('email_confirmations_enabled') ? 1 : 0;
        }
        if ($request->has('email_notifications_enabled')) {
            $data['email_notifications_enabled'] = $request->boolean('email_notifications_enabled') ? 1 : 0;
        }

        $ssoKeys = [
            'siapkerja_client_id',
            'siapkerja_client_secret',
            'siapkerja_redirect',
            'siapkerja_scope',
            'siapkerja_api_base',
            'siapkerja_service_base',
            'siapkerja_service_token',
            'siapkerja_service_program_id',
            'siapkerja_service_schedule_id',
            'siapkerja_service_instructor_id',
            'siapkerja_service_participant_id',
            'siapkerja_service_pencaker_id',
            'siapkerja_service_city_code',
            'siapkerja_service_prov_code',
            'siapkerja_service_vocational',
            'siapkerja_service_sub_vocational',
            'siapkerja_profile_api_base',
            'siapkerja_token_url',
            'siapkerja_profile_url',
        ];
        foreach ($ssoKeys as $key) {
            if (array_key_exists($key, $data) && is_string($data[$key]) && trim($data[$key]) === '') {
                unset($data[$key]);
            }
        }
        if (array_key_exists('siapkerja_redirect', $data)) {
            $data['siapkerja_redirect'] = preg_replace('~(?<!:)/{2,}~', '/', trim($data['siapkerja_redirect']));
        }

        if (array_key_exists('mail_password', $data) && $data['mail_password'] === '') {
            unset($data['mail_password']);
        }
        if (array_key_exists('footer_embed_map', $data) && $data['footer_embed_map']) {
            $data['footer_embed_map'] = HtmlSanitizer::cleanEmbed($data['footer_embed_map']);
        }

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            cache()->forget("site_setting:{$key}");
        }

        return redirect()->back()->with('success', 'Pengaturan situs disimpan.');
    }
}
