<?php
declare(strict_types=1);
namespace PhpJs\IR\Effect;

use InvalidArgumentException;

final class EventTrigger implements EffectTrigger {
    public function __construct(
        public readonly string $on,         // 'self' | 'window' | 'document'
        public readonly string $event,
        public readonly ?array $options = null
    ) {
        if (!in_array($this->on, ['self','window','document'], true)) throw new InvalidArgumentException('EventTrigger: on must be self|window|document');
        if (trim($this->event) === '') throw new InvalidArgumentException('EventTrigger: event cannot be empty');
        if ($this->options !== null && array_values($this->options) === $this->options) throw new InvalidArgumentException('EventTrigger: options must be associative or null');
    }
    public function type(): string { return 'event'; }
    public function jsonSerialize(): array {
        $out = ['type'=>'event','on'=>$this->on,'event'=>$this->event];
        if ($this->options !== null) $out['options'] = $this->options;
        return $out;
    }
}
