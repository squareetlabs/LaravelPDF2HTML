<?php

namespace Squareetlabs\PdfToHtml\Tests;

use Squareetlabs\PdfToHtml\Services\CssInliner;

class CssInlinerTest extends TestCase
{
    public function testItInlinesCss()
    {
        $html = '<html><style>.red { color: red; }</style><body><div class="red">Hello</div></body></html>';
        $inliner = CssInliner::fromHtml($html);
        $result = $inliner->inlineCss()->render();

        $this->assertStringContainsString('style="color: red"', $result);
        $this->assertStringNotContainsString('<style>', $result);
    }

    public function testItHandlesMultipleSelectors()
    {
        $html = '<html><style>.red, .bold { color: red; font-weight: bold; }</style><body><div class="red">Hello</div><span class="bold">World</span></body></html>';
        $inliner = CssInliner::fromHtml($html);
        $result = $inliner->inlineCss()->render();

        $this->assertStringContainsString('style="color: red; font-weight: bold"', $result);
    }

    public function testItHandlesIdSelectors()
    {
        $html = '<html><style>#main { padding: 10px; }</style><body><div id="main">Content</div></body></html>';
        $result = CssInliner::fromHtml($html)->inlineCss()->render();

        $this->assertStringContainsString('style="padding: 10px"', $result);
    }
}
?>