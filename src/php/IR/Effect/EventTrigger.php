<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

use InvalidArgumentException;

/**
 * Effect trigger for DOM events.
 *
 * Supports self, window, or document event sources.
 *
 * @group IR/Effect
 * @example
 * $trigger = new EventTrigger('window', 'resize');
 */
final class EventTrigger implements EffectTrigger {
    /**
     * Build an event trigger.
     *
     * @param string $on Event source: self|window|document.
     * @param string $event Event name.
     * @param array<string, mixed>|null $options Optional listener options.
     */
    public function __construct(
        public readonly string $on,         // 'self' | 'window' | 'document'
        public readonly string $event,
        public readonly ?array $options = null
    ) {
        if (!in_array($this->on, ['self','window','document'], true)) throw new InvalidArgumentException('EventTrigger: on must be self|window|document');
        if (trim($this->event) === '') throw new InvalidArgumentException('EventTrigger: event cannot be empty');
        if ($this->options !== null && array_values($this->options) === $this->options) throw new InvalidArgumentException('EventTrigger: options must be associative or null');
    }

    /**
     * Return trigger discriminator.
     *
     * @return string
     */
    public function type(): string { return 'event'; }

    /**
     * Encode this trigger as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        $out = ['type'=>'event','on'=>$this->on,'event'=>$this->event];
        if ($this->options !== null) $out['options'] = $this->options;
        return $out;
    }
}
