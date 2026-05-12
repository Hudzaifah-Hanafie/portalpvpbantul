<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    /** @var array<string, HTMLPurifier> */
    private static array $purifiers = [];

    public static function clean(?string $html): string
    {
        return self::purify($html, self::defaultConfig());
    }

    public static function cleanEmbed(?string $html): string
    {
        return self::purify($html, self::embedConfig());
    }

    private static function purify(?string $html, array $config): string
    {
        $html = (string) ($html ?? '');
        if ($html === '') {
            return '';
        }

        if (class_exists(HTMLPurifier::class)) {
            $key = md5(json_encode($config));
            if (! isset(self::$purifiers[$key])) {
                $purifierConfig = HTMLPurifier_Config::createDefault();
                $purifierConfig->set('HTML.Allowed', $config['allowed'] ?? '');
                $purifierConfig->set('AutoFormat.AutoParagraph', false);
                $purifierConfig->set('AutoFormat.RemoveEmpty', true);
                $purifierConfig->set('Attr.AllowedFrameTargets', ['_blank']);

                if (! empty($config['safe_iframe'])) {
                    $purifierConfig->set('HTML.SafeIframe', true);
                    $purifierConfig->set('URI.SafeIframeRegexp', $config['safe_iframe']);
                }

                self::$purifiers[$key] = new HTMLPurifier($purifierConfig);
            }

            return self::$purifiers[$key]->purify($html);
        }

        return self::stripDangerous($html, $config['allowed_tags'] ?? '');
    }

    private static function stripDangerous(string $html, string $allowedTags): string
    {
        $clean = strip_tags($html, $allowedTags);
        $clean = preg_replace('/\son\w+\s*=\s*(\"[^\"]*\"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\s(href|src)\s*=\s*(\"|\')\s*javascript:[^\2]*\2/i', '', $clean) ?? $clean;

        return $clean ?? '';
    }

    private static function defaultConfig(): array
    {
        return [
            'allowed' => 'p,br,strong,em,b,i,u,ul,ol,li,a[href|title|target|rel],blockquote,code,pre',
            'allowed_tags' => '<p><br><strong><em><b><i><u><ul><ol><li><a><blockquote><code><pre>',
        ];
    }

    private static function embedConfig(): array
    {
        return [
            'allowed' => 'iframe[src|width|height|frameborder|allow|allowfullscreen|loading|referrerpolicy],a[href|title|target|rel],div[class],span[class],p,br,strong,em',
            'safe_iframe' => '#^https?://#',
            'allowed_tags' => '<iframe><a><div><span><p><br><strong><em>',
        ];
    }
}
