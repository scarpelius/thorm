<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use function Thorm\{concat, eq, get, mod, read, state, thorm, transphorm, val};

final class IrShapeTest extends TestCase
{
    public function test_val_serializes_as_literal_expression(): void
    {
        $json = val('hello')->jsonSerialize();

        $this->assertSame('val', $json['k']);
        $this->assertSame('hello', $json['v']);
    }

    public function test_get_serializes_as_get_operation_with_normalized_operands(): void
    {
        $json = get(['message' => 'hi'], 'message')->jsonSerialize();

        $a = $json['a']->jsonSerialize();
        $b = $json['b']->jsonSerialize();

        $this->assertSame('op', $json['k']);
        $this->assertSame('get', $json['name']);
        $this->assertSame('val', $a['k']);
        $this->assertSame(['message' => 'hi'], $a['v']);
        $this->assertSame('val', $b['k']);
        $this->assertSame('message', $b['v']);
    }

    public function test_concat_serializes_all_parts_in_order(): void
    {
        $count = state(2);
        $json = concat('count: ', read($count))->jsonSerialize();

        $zero = $json['parts'][0]->jsonSerialize();
        $one = $json['parts'][1]->jsonSerialize();

        $this->assertSame('concat', $json['k']);
        $this->assertCount(2, $json['parts']);
        $this->assertSame('val', $zero['k']);
        $this->assertSame('count: ', $zero['v']);
        $this->assertSame('read', $one['k']);
        $this->assertSame($count->id, $one['atom']);
    }

    public function test_eq_serializes_as_eq_operation(): void
    {
        $json = eq(3, 3)->jsonSerialize();

        $a = $json['a']->jsonSerialize();
        $b = $json['b']->jsonSerialize();

        $this->assertSame('op', $json['k']);
        $this->assertSame('eq', $json['name']);
        $this->assertSame(3, $a['v']);
        $this->assertSame(3, $b['v']);
    }

    public function test_mod_serializes_as_mod_operation(): void
    {
        $json = mod(10, 4)->jsonSerialize();

        $a = $json['a']->jsonSerialize();
        $b = $json['b']->jsonSerialize();

        $this->assertSame('op', $json['k']);
        $this->assertSame('mod', $json['name']);
        $this->assertSame(10, $a['v']);
        $this->assertSame(4, $b['v']);
    }

    public function test_thorm_aliases_plus_to_add_operation(): void
    {
        $json = thorm(['+', 2, 5])->jsonSerialize();

        $a = $json['a']->jsonSerialize();
        $b = $json['b']->jsonSerialize();

        $this->assertSame('op', $json['k']);
        $this->assertSame('add', $json['name']);
        $this->assertSame(2, $a['v']);
        $this->assertSame(5, $b['v']);
    }

    public function test_thorm_read_with_non_atom_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('thorm(): read expects an Atom.');

        thorm(['read', 123]);
    }

    public function test_transphorm_is_an_alias_of_thorm(): void
    {
        $json = transphorm(['%', 9, 4])->jsonSerialize();

        $a = $json['a']->jsonSerialize();
        $b = $json['b']->jsonSerialize();

        $this->assertSame('op', $json['k']);
        $this->assertSame('mod', $json['name']);
        $this->assertSame(9, $a['v']);
        $this->assertSame(4, $b['v']);
    }
}
