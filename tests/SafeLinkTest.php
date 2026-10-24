<?php

namespace Harimayco\Menu\Tests;

use Harimayco\Menu\Rules\SafeLink;

class SafeLinkTest extends TestCase
{
    public function test_allows_relative_and_safe_links()
    {
        $rule = new SafeLink();

        foreach (['/about', 'about', '#top', '?page=2', '//cdn.test/x', 'http://a.test', 'HTTPS://a.test', 'mailto:a@b.c', 'tel:+84123', ''] as $link) {
            $this->assertTrue($rule->passes('link', $link), $link);
        }
    }

    public function test_rejects_script_links()
    {
        $rule = new SafeLink();

        foreach (['javascript:alert(1)', 'JaVaScRiPt:alert(1)', ' javascript:x', "java\tscript:x", "java\nscript:x", 'data:text/html,x', 'vbscript:x'] as $link) {
            $this->assertFalse($rule->passes('link', $link), $link);
        }
    }

    public function test_schemes_can_be_customised()
    {
        $this->assertTrue((new SafeLink(['ftp']))->passes('link', 'ftp://a.test'));
        $this->assertFalse((new SafeLink(['ftp']))->passes('link', 'http://a.test'));
    }
}
