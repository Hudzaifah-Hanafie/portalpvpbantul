<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicationSetting;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class PublicationSettingController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function edit()
    {
        $setting = PublicationSetting::first() ?? new PublicationSetting();
        return view('admin.publication.settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero_title' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string|max:1000',
            'hero_button_text' => 'nullable|string|max:100',
            'hero_button_link' => 'nullable|string|max:255',
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'intro_title' => 'nullable|string|max:255',
            'intro_description' => 'nullable|string|max:1000',
            'alumni_title' => 'nullable|string|max:255',
            'alumni_description' => 'nullable|string|max:1000',
            'alumni_video_url' => ['nullable','url','max:255','regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/i'],
            'downloads_title' => 'nullable|string|max:255',
            'downloads_description' => 'nullable|string|max:1000',
        ], [
            'alumni_video_url.regex' => 'URL video harus berasal dari YouTube.',
        ]);

        if (! empty($data['alumni_video_url'])) {
            $data['alumni_video_url'] = $this->normalizeYoutubeUrl($data['alumni_video_url']);
        }

        $setting = PublicationSetting::first() ?? new PublicationSetting();
        if ($request->hasFile('hero_image')) {
            if ($setting->hero_image) {
                $oldPath = str_replace('/storage/', '', $setting->hero_image);
                Storage::disk('public')->delete($oldPath);
            }
            $data['hero_image'] = '/storage/' . $request->file('hero_image')->store('publications', 'public');
        }
        $setting->fill($data)->save();

        $this->logger->log(
            $request->user(),
            'publication_setting.updated',
            'Pengaturan publikasi diperbarui',
            $setting
        );

        return redirect()->back()->with('success', 'Pengaturan publikasi disimpan.');
    }

    private function normalizeYoutubeUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (! isset($parsed['host'])) {
            return $url;
        }

        $host = $parsed['host'];
        if (str_contains($host, 'youtu.be')) {
            $videoId = ltrim($parsed['path'] ?? '', '/');
            return $videoId ? 'https://www.youtube.com/embed/' . $videoId : $url;
        }

        parse_str($parsed['query'] ?? '', $query);
        if (isset($query['v'])) {
            return 'https://www.youtube.com/embed/' . $query['v'];
        }

        return $url;
    }
}
