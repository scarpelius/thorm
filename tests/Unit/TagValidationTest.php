<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thorm\IR\Node\ElNode;
use function Thorm\{_tag_validate, attrs, div, ul, li, span, linkTag, text};

final class TagValidationTest extends TestCase
{
    public function test_valid_tag_helper_returns_el_node(): void
    {
        $node = div([attrs(['id' => 'app'])], [text('hello')]);

        $this->assertInstanceOf(ElNode::class, $node);
        $this->assertSame('div', $node->tag);
    }

    public function test_valid_child_combination_is_allowed(): void
    {
        $node = ul([], [
            li([], [text('first')]),
            li([], [text('second')]),
        ]);

        $this->assertInstanceOf(ElNode::class, $node);
        $this->assertSame('ul', $node->tag);
        $this->assertCount(2, $node->children);
    }

    public function test_invalid_direct_child_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tag <ul> does not allow <span> as a direct child.');

        ul([], [span()]);
    }

    public function test_invalid_attribute_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Tag <div> does not allow attribute 'href'.");

        div([attrs(['href' => '/nope'])]);
    }

    public function test_data_attributes_are_allowed(): void
    {
        $node = div([attrs(['data-test' => 'ok'])]);
        $json = $node->jsonSerialize();

        $this->assertSame('val', $json['props']['attrs']['data-test']['k']);
        $this->assertSame('ok', $json['props']['attrs']['data-test']['v']);
    }

    public function test_aria_attributes_are_allowed(): void
    {
        $node = div([attrs(['aria-label' => 'Greeting'])]);
        $json = $node->jsonSerialize();

        $this->assertSame('val', $json['props']['attrs']['aria-label']['k']);
        $this->assertSame('Greeting', $json['props']['attrs']['aria-label']['v']);
    }

    public function test_void_tag_rejects_children(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tag <img> does not allow children.');

        _tag_validate('img', [], [span()]);
    }

    public function test_special_renamed_helper_builds_expected_tag(): void
    {
        $node = linkTag([attrs(['rel' => 'stylesheet', 'href' => '/app.css'])]);

        $this->assertInstanceOf(ElNode::class, $node);
        $this->assertSame('link', $node->tag);
    }
}
