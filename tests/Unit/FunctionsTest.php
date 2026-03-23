<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thorm\IR\Node\ElNode;
use function Thorm\{state, read, val, cmp, client, text, thorm};

final class FunctionsTest extends TestCase
{
    public function test_state_and_read_build_expression(): void
    {
        $atom = state(3);
        $expr = read($atom);

        $json = $expr->jsonSerialize();

        $this->assertSame('read', $json['k']);
        $this->assertArrayHasKey('atom', $json);
        $this->assertSame($atom->id, $json['atom']);
    }

    public function test_thorm_rejects_unknown_ops(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        thorm(['nope', 1, 2]);
    }

    public function test_cmp_rejects_unknown_operator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        cmp('===', val(1), val(1));
    }

    public function test_client_requires_element_node(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        client(text('x'));
    }

    public function test_client_marks_element_for_client_rendering(): void
    {
        $node = client(new ElNode('div', [], []));
        $json = $node->jsonSerialize();

        $this->assertSame('client', $json['render']['target']);
    }
}
