<?php
declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Thorm\Render;
use function Thorm\{client, component, el, fragment, html, inc, item, onMount, prop, read, repeat, route, show, slot, state, text};

final class RenderIntegrationTest extends TestCase
{
    public function test_render_renders_nested_tree_to_html(): void
    {
        $name = state('Thorm');
        $app = el('div', [], [
            el('h1', [], [text('Hello')]),
            el('p', [], [text(read($name))]),
        ]);

        $html = (new Render())->render($app)['html'];

        $this->assertStringContainsString('<div>', $html);
        $this->assertStringContainsString('<h1>Hello</h1>', $html);
        $this->assertStringContainsString('<p>Thorm</p>', $html);
    }

    public function test_render_show_true_includes_child_between_markers(): void
    {
        $app = show(true, text('Visible'));

        $html = (new Render())->render($app)['html'];

        $this->assertSame('<!--show:start-->Visible<!--show:end-->', $html);
    }

    public function test_render_show_false_omits_child_but_keeps_markers(): void
    {
        $app = show(false, text('Hidden'));

        $html = (new Render())->render($app)['html'];

        $this->assertSame('<!--show:start--><!--show:end-->', $html);
    }

    public function test_render_repeat_uses_atom_override_data(): void
    {
        $rows = state([]);
        $app = repeat(read($rows), item('id'), el('span', [], [text(item('label'))]));

        $html = (new Render())->render($app, [
            'atoms' => [
                $rows->id => [
                    ['id' => 1, 'label' => 'One'],
                    ['id' => 2, 'label' => 'Two'],
                ],
            ],
        ])['html'];

        $this->assertStringContainsString('<!--repeat:start-->', $html);
        $this->assertStringContainsString('<!--repeat:row:1:start--><span>One</span><!--repeat:row:1:end-->', $html);
        $this->assertStringContainsString('<!--repeat:row:2:start--><span>Two</span><!--repeat:row:2:end-->', $html);
        $this->assertStringContainsString('<!--repeat:end-->', $html);
    }

    public function test_render_client_target_outputs_shell_without_server_children(): void
    {
        $app = client(el('div', [], [text('Hydrate me')]));

        $html = (new Render())->render($app)['html'];

        $this->assertSame('<div></div>', $html);
    }

    public function test_render_effect_node_outputs_no_visible_html(): void
    {
        $count = state(1);
        $app = fragment([
            text('Before'),
            onMount([inc($count, 1, true)]),
            text('After'),
        ]);

        $html = (new Render())->render($app)['html'];

        $this->assertSame('<!--fragment:start-->BeforeAfter<!--fragment:end-->', $html);
    }

    public function test_render_route_outputs_only_markers_on_php_side(): void
    {
        $app = route([
            '/' => text('Home'),
            '/posts/:id' => text('Post'),
        ], text('Not found'));

        $html = (new Render())->render($app)['html'];

        $this->assertSame('<!--route:start--><!--route:end-->', $html);
    }

    public function test_render_html_node_outputs_raw_markup_between_markers(): void
    {
        $app = html('<strong>Unsafe?</strong>');

        $html = (new Render())->render($app)['html'];

        $this->assertSame('<!--html:start--><strong>Unsafe?</strong><!--html:end-->', $html);
    }

    public function test_render_component_renders_template_with_explicit_props(): void
    {
        $card = el('section', [], [
            el('h2', [], [text(prop('title'))]),
            el('p', [], [text(prop('body'))]),
        ]);

        $app = component($card, [
            'title' => \Thorm\val('Welcome'),
            'body' => \Thorm\val('Rendered from props'),
        ]);

        $html = (new Render())->render($app)['html'];

        $this->assertSame(
            '<!--component:start--><section><h2>Welcome</h2><p>Rendered from props</p></section><!--component:end-->',
            $html
        );
    }

    public function test_render_component_renders_default_slot_content(): void
    {
        $panel = el('div', [], [
            el('header', [], [text('Panel')]),
            el('main', [], [slot()]),
        ]);

        $app = component($panel, [], [
            el('p', [], [text('Slot body')]),
        ]);

        $html = (new Render())->render($app)['html'];

        $this->assertSame(
            '<!--component:start--><div><header>Panel</header><main><!--slot:start--><p>Slot body</p><!--slot:end--></main></div><!--component:end-->',
            $html
        );
    }

    public function test_render_component_renders_named_slot_content(): void
    {
        $layout = el('article', [], [
            el('header', [], [slot('title')]),
            el('section', [], [slot('content')]),
        ]);

        $app = component($layout, [], [
            'title' => [el('h1', [], [text('Named title')])],
            'content' => [el('p', [], [text('Named content')])],
        ]);

        $html = (new Render())->render($app)['html'];

        $this->assertSame(
            '<!--component:start--><article><header><!--slot:start--><h1>Named title</h1><!--slot:end--></header><section><!--slot:start--><p>Named content</p><!--slot:end--></section></article><!--component:end-->',
            $html
        );
    }
}
