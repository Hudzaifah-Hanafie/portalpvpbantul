<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_clean_strips_scripts_and_event_handlers(): void
    {
        $dirty = '<p>Halo <strong>Teman</strong><script>alert(1)</script>'
            . '<img src="x" onerror="alert(1)"><a href="javascript:alert(1)">Link</a></p>';

        $clean = HtmlSanitizer::clean($dirty);

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', strtolower($clean));
        $this->assertStringContainsString('<strong>', $clean);
    }

    public function test_clean_embed_allows_iframe_with_attributes(): void
    {
        $dirty = '<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
        $clean = HtmlSanitizer::cleanEmbed($dirty);
        $this->assertStringContainsString('iframe', $clean);
        $this->assertStringContainsString('allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"', $clean);
        $this->assertStringContainsString('allowfullscreen', $clean);
        $this->assertStringContainsString('loading="lazy"', $clean);
        $this->assertStringContainsString('referrerpolicy="no-referrer-when-downgrade"', $clean);
    }
}

