<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use function Thorm\{attrs, el, fragment, html, item, link, read, repeat, route, show, state, text};

final class NodeSerializationTest extends TestCase
{
    public function test_el_serializes_tag_props_and_children(): void
    {
        $node = el('div', [attrs(['id' => 'app'])], [text('hello')]);
        $json = $node->jsonSerialize();

        $this->assertSame('el', $json['k']);
        $this->assertSame('div', $json['tag']);
        $this->assertSame('val', $json['props']['attrs']['id']['k']);
        $this->assertSame('app', $json['props']['attrs']['id']['v']);
        $this->assertCount(1, $json['children']);
        $this->assertSame('text', $json['children'][0]->jsonSerialize()['k']);
    }

    public function test_text_serializes_literal_value(): void
    {
        $json = text('hello')->jsonSerialize();

        $this->assertSame('text', $json['k']);
        $this->assertSame('val', $json['value']['k']);
        $this->assertSame('hello', $json['value']['v']);
    }

    public function test_html_serializes_literal_value(): void
    {
        $json = html('<strong>Hello</strong>')->jsonSerialize();

        $this->assertSame('html', $json['k']);
        $this->assertSame('val', $json['value']['k']);
        $this->assertSame('<strong>Hello</strong>', $json['value']['v']);
    }

    public function test_show_serializes_when_and_child(): void
    {
        $json = show(true, text('Visible'))->jsonSerialize();

        $this->assertSame('show', $json['k']);
        $this->assertSame('val', $json['when']['k']);
        $this->assertTrue($json['when']['v']);
        $this->assertSame('text', $json['child']->jsonSerialize()['k']);
    }

    public function test_fragment_serializes_children_array(): void
    {
        $json = fragment([text('A'), text('B')])->jsonSerialize();

        $this->assertSame('fragment', $json['k']);
        $this->assertCount(2, $json['children']);
        $this->assertSame('text', $json['children'][0]->jsonSerialize()['k']);
        $this->assertSame('text', $json['children'][1]->jsonSerialize()['k']);
    }

    public function test_repeat_serializes_items_key_and_template(): void
    {
        $items = state([
            ['id' => 1, 'label' => 'One'],
            ['id' => 2, 'label' => 'Two'],
        ]);

        $json = repeat(read($items), item('id'), text('row'))->jsonSerialize();

        $this->assertSame('repeat', $json['k']);
        $this->assertSame('read', $json['items']->jsonSerialize()['k']);
        $this->assertSame($items->id, $json['items']->jsonSerialize()['atom']);
        $this->assertSame('item', $json['key']->jsonSerialize()['k']);
        $this->assertSame('id', $json['key']->jsonSerialize()['path']);
        $this->assertSame('text', $json['tpl']->jsonSerialize()['k']);
    }

    public function test_link_serializes_destination_props_and_children(): void
    {
        $json = link('/docs', [attrs(['target' => '_blank'])], [text('Docs')])->jsonSerialize();

        $this->assertSame('link', $json['k']);
        $this->assertSame('val', $json['to']->jsonSerialize()['k']);
        $this->assertSame('/docs', $json['to']->jsonSerialize()['v']);
        $this->assertSame('attrs', $json['props'][0][0]);
        $this->assertSame('_blank', $json['props'][0][1]['target']);
        $this->assertSame('text', $json['children'][0]->jsonSerialize()['k']);
    }

    public function test_route_serializes_base_table_views_and_fallback(): void
    {
        $json = route([
            '/' => text('Home'),
            '/posts/:id' => text('Post'),
        ], text('Not found'), '/app')->jsonSerialize();

        $this->assertSame('route', $json['k']);
        $this->assertSame('/app', $json['base']);
        $this->assertCount(2, $json['table']);
        $this->assertSame('/', $json['table'][0]['pat']);
        $this->assertSame('/posts/:id', $json['table'][1]['pat']);
        $this->assertSame(['id'], $json['table'][1]['keys']);
        $this->assertCount(2, $json['views']);
        $this->assertSame('text', $json['views'][0]->jsonSerialize()['k']);
        $this->assertSame('text', $json['fallback']->jsonSerialize()['k']);
    }
}
