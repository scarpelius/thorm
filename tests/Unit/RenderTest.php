<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thorm\Render;
use function Thorm\{el, text, state, read, client};

final class RenderTest extends TestCase
{
    public function test_to_ir_collects_atoms_and_root(): void
    {
        $count = state(1);
        $app = el('div', [], [text(read($count))]);

        $render = new Render();
        $ir = $render->toIR($app);

        $this->assertArrayHasKey('atoms', $ir);
        $this->assertArrayHasKey('root', $ir);
        $this->assertCount(1, $ir['atoms']);
        $this->assertSame('el', $ir['root']['k']);
    }

    public function test_render_returns_html_state_and_ir(): void
    {
        $count = state(5);
        $app = el('div', [], [text(read($count))]);

        $render = new Render();
        $res = $render->render($app);

        $this->assertArrayHasKey('html', $res);
        $this->assertArrayHasKey('state', $res);
        $this->assertArrayHasKey('ir', $res);
        $this->assertStringContainsString('5', $res['html']);
    }

    public function test_render_applies_atom_overrides(): void
    {
        $count = state(5);
        $app = el('div', [], [text(read($count))]);

        $render = new Render();
        $res = $render->render($app, [
            'atoms' => [$count->id => 9],
        ]);

        $this->assertStringContainsString('9', $res['html']);
    }

    public function test_client_render_flag_is_preserved_in_ir(): void
    {
        $app = client(el('div', [], [text('hello')]));

        $render = new Render();
        $ir = $render->toIR($app);

        $this->assertSame('client', $ir['root']['render']['target']);
    }
}
