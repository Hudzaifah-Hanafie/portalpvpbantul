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
}
