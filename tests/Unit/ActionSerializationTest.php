<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use function Thorm\{add, delay, http, inc, navigate, redirect, set, state, val};

final class ActionSerializationTest extends TestCase
{
    public function test_inc_listener_serializes_expected_shape(): void
    {
        $count = state(1);
        $json = inc($count, 2)->jsonSerialize();

        $this->assertSame('inc', $json['k']);
        $this->assertSame($count->id, $json['atom']);
        $this->assertSame(2, $json['by']);
    }

    public function test_inc_action_serializes_expected_shape(): void
    {
        $count = state(1);
        $json = inc($count, 3, true)->jsonSerialize();

        $this->assertSame('inc', $json['k']);
        $this->assertSame($count->id, $json['atom']);
        $this->assertSame(3, $json['by']);
    }

    public function test_set_listener_serializes_expr_payload(): void
    {
        $count = state(1);
        $json = set($count, 'done')->jsonSerialize();

        $this->assertSame('set', $json['k']);
        $this->assertSame($count->id, $json['atom']);
        $this->assertSame('val', $json['to']->jsonSerialize()['k']);
        $this->assertSame('done', $json['to']->jsonSerialize()['v']);
    }

    public function test_set_action_serializes_expected_shape(): void
    {
        $count = state(1);
        $json = set($count, 'done', true)->jsonSerialize();

        $this->assertSame('set', $json['k']);
        $this->assertSame($count->id, $json['atom']);
        $this->assertSame('done', $json['to']);
    }

    public function test_add_listener_returns_add_expression(): void
    {
        $count = state(1);
        $json = add($count, val(4))->jsonSerialize();

        $this->assertSame('op', $json['k']);
        $this->assertSame('add', $json['name']);
        $this->assertSame('read', $json['a']->jsonSerialize()['k']);
        $this->assertSame($count->id, $json['a']->jsonSerialize()['atom']);
        $this->assertSame('val', $json['b']->jsonSerialize()['k']);
        $this->assertSame(4, $json['b']->jsonSerialize()['v']);
    }

    public function test_add_action_serializes_expected_shape(): void
    {
        $count = state(1);
        $json = add($count, 4, true)->jsonSerialize();

        $this->assertSame('add', $json['k']);
        $this->assertSame($count->id, $json['atom']);
        $this->assertSame(4, $json['by']);
    }

    public function test_delay_serializes_nested_actions(): void
    {
        $count = state(1);
        $json = delay(250, [inc($count, 1, true), set($count, 5, true)])->jsonSerialize();

        $this->assertSame('delay', $json['k']);
        $this->assertSame(250, $json['ms']);
        $this->assertCount(2, $json['actions']);
        $this->assertSame('inc', $json['actions'][0]->jsonSerialize()['k']);
        $this->assertSame('set', $json['actions'][1]->jsonSerialize()['k']);
    }

    public function test_navigate_listener_serializes_destination_expr(): void
    {
        $json = navigate('/docs')->jsonSerialize();

        $this->assertSame('navigate', $json['k']);
        $this->assertSame('val', $json['to']->jsonSerialize()['k']);
        $this->assertSame('/docs', $json['to']->jsonSerialize()['v']);
    }

    public function test_navigate_action_serializes_destination_value(): void
    {
        $json = navigate('/docs', true)->jsonSerialize();

        $this->assertSame('navigate', $json['k']);
        $this->assertSame('/docs', $json['to']);
    }

    public function test_redirect_serializes_replace_flag_only_when_true(): void
    {
        $default = redirect('/login')->jsonSerialize();
        $replace = redirect('/login', true)->jsonSerialize();

        $this->assertSame('redirect', $default['k']);
        $this->assertSame('/login', $default['to']);
        $this->assertArrayNotHasKey('replace', $default);

        $this->assertSame('redirect', $replace['k']);
        $this->assertSame('/login', $replace['to']);
        $this->assertTrue($replace['replace']);
    }

    public function test_http_listener_serializes_required_fields(): void
    {
        $out = state(null);
        $status = state(null);
        $json = http('/api/ping', 'post', $out, $status, ['X-Test' => '1'], ['ok' => true])->jsonSerialize();

        $this->assertSame('http', $json['k']);
        $this->assertSame('POST', $json['method']);
        $this->assertSame('val', $json['url']->jsonSerialize()['k']);
        $this->assertSame('/api/ping', $json['url']->jsonSerialize()['v']);
        $this->assertSame($out->id, $json['to']);
        $this->assertSame($status->id, $json['status']);
        $this->assertSame('val', $json['headers']['X-Test']->jsonSerialize()['k']);
        $this->assertSame('1', $json['headers']['X-Test']->jsonSerialize()['v']);
        $this->assertSame('val', $json['body']->jsonSerialize()['k']);
        $this->assertSame(['ok' => true], $json['body']->jsonSerialize()['v']);
    }

    public function test_http_action_serializes_request_and_response_targets(): void
    {
        $out = state(null);
        $status = state(null);
        $headers = state(null);
        $json = http('/api/ping', 'post', $out, $status, ['X-Test' => val('1')], val('payload'), 'json', true, $headers)->jsonSerialize();
        $json['url'] = val($json['url'])->jsonSerialize();
        
        $this->assertSame('http', $json['k']);
        $this->assertSame('POST', $json['method']);
        $this->assertSame('val', $json['url']['k']);
        $this->assertSame('/api/ping', $json['url']['v']);
        $this->assertSame($out->id, $json['to']);
        $this->assertSame($status->id, $json['status']);
        $this->assertSame($headers->id, $json['resHeaders']);
        $this->assertSame('val', $json['reqHeaders']['X-Test']['k']);
        $this->assertSame('1', $json['reqHeaders']['X-Test']['v']);
        $this->assertSame('val', $json['body']['k']);
        $this->assertSame('payload', $json['body']['v']);
    }
}
