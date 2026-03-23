<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use function Thorm\{after, documentTarget, every, inc, onDocument, onMount, onSelf, onSse, onVisible, onWindow, read, selectorTarget, state, val, watch, windowTarget};

final class EffectSerializationTest extends TestCase
{
    public function test_on_mount_serializes_mount_trigger_and_action(): void
    {
        $count = state(1);
        $json = onMount([inc($count, 1, true)])->jsonSerialize();

        $this->assertSame('effect', $json['k']);
        $this->assertCount(1, $json['triggers']);
        $this->assertSame('mount', $json['triggers'][0]['type']);
        $this->assertCount(1, $json['actions']);
        $this->assertSame('inc', $json['actions'][0]['k']);
    }

    public function test_watch_serializes_expr_and_options(): void
    {
        $count = state(1);
        $json = watch(read($count), [inc($count, 1, true)], true, 120, 300)->jsonSerialize();

        $this->assertSame('effect', $json['k']);
        $this->assertSame('watch', $json['triggers'][0]['type']);
        $this->assertSame('read', $json['triggers'][0]['expr']['k']);
        $this->assertSame($count->id, $json['triggers'][0]['expr']['atom']);
        $this->assertTrue($json['triggers'][0]['immediate']);
        $this->assertSame(120, $json['triggers'][0]['debounceMs']);
        $this->assertSame(300, $json['triggers'][0]['throttleMs']);
    }

    public function test_every_serializes_interval_trigger(): void
    {
        $count = state(1);
        $json = every(500, [inc($count, 1, true)])->jsonSerialize();

        $this->assertSame('effect', $json['k']);
        $this->assertSame('interval', $json['triggers'][0]['type']);
        $this->assertSame(500, $json['triggers'][0]['ms']);
    }

    public function test_after_serializes_timeout_trigger(): void
    {
        $count = state(1);
        $json = after(250, [inc($count, 1, true)])->jsonSerialize();

        $this->assertSame('effect', $json['k']);
        $this->assertSame('timeout', $json['triggers'][0]['type']);
        $this->assertSame(250, $json['triggers'][0]['ms']);
    }

    public function test_on_visible_serializes_threshold_root_margin_and_target(): void
    {
        $count = state(1);
        $json = onVisible([inc($count, 1, true)], 0.5, '10px', selectorTarget('#app'))->jsonSerialize();

        $this->assertSame('effect', $json['k']);
        $this->assertSame('visible', $json['triggers'][0]['type']);
        $this->assertSame(0.5, $json['triggers'][0]['threshold']);
        $this->assertSame('10px', $json['triggers'][0]['rootMargin']);
        $this->assertSame('enter', $json['triggers'][0]['when']);
        $this->assertSame('selector', $json['target']['type']);
        $this->assertSame('#app', $json['target']['selector']);
    }

    public function test_on_window_serializes_event_trigger(): void
    {
        $count = state(1);
        $json = onWindow('resize', [inc($count, 1, true)], ['passive' => true])->jsonSerialize();

        $this->assertSame('effect', $json['k']);
        $this->assertSame('event', $json['triggers'][0]['type']);
        $this->assertSame('window', $json['triggers'][0]['on']);
        $this->assertSame('resize', $json['triggers'][0]['event']);
        $this->assertTrue($json['triggers'][0]['options']['passive']);
        $this->assertArrayNotHasKey('target', $json);
    }

    public function test_on_document_serializes_event_trigger(): void
    {
        $count = state(1);
        $json = onDocument('keydown', [inc($count, 1, true)], ['capture' => true])->jsonSerialize();

        $this->assertSame('event', $json['triggers'][0]['type']);
        $this->assertSame('document', $json['triggers'][0]['on']);
        $this->assertSame('keydown', $json['triggers'][0]['event']);
        $this->assertTrue($json['triggers'][0]['options']['capture']);
    }

    public function test_on_self_serializes_event_trigger_and_explicit_target(): void
    {
        $count = state(1);
        $json = onSelf('click', [inc($count, 1, true)], ['once' => true], windowTarget())->jsonSerialize();

        $this->assertSame('event', $json['triggers'][0]['type']);
        $this->assertSame('self', $json['triggers'][0]['on']);
        $this->assertSame('click', $json['triggers'][0]['event']);
        $this->assertTrue($json['triggers'][0]['options']['once']);
        $this->assertSame('window', $json['target']['type']);
    }

    public function test_on_sse_serializes_stream_trigger_and_target(): void
    {
        $count = state(1);
        $json = onSse('/events', [inc($count, 1, true)], 'update', 'text', true, val(42), documentTarget())->jsonSerialize();

        $this->assertSame('effect', $json['k']);
        $this->assertSame('sse', $json['triggers'][0]['type']);
        $this->assertSame('val', $json['triggers'][0]['url']['k']);
        $this->assertSame('/events', $json['triggers'][0]['url']['v']);
        $this->assertSame('update', $json['triggers'][0]['event']);
        $this->assertSame('text', $json['triggers'][0]['parse']);
        $this->assertTrue($json['triggers'][0]['withCredentials']);
        $this->assertSame('val', $json['triggers'][0]['sinceId']['k']);
        $this->assertSame(42, $json['triggers'][0]['sinceId']['v']);
        $this->assertSame('document', $json['target']['type']);
    }
}
