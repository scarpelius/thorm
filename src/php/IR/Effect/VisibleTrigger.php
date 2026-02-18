<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

use InvalidArgumentException;

/**
 * Effect trigger based on element visibility.
 *
 * @group IR/Effect
 * @example
 * $trigger = new VisibleTrigger(0.5, '0px', 'enter');
 */
final class VisibleTrigger implements EffectTrigger {
    /**
     * Build a visibility trigger.
     *
     * @param float $threshold Intersection threshold between 0 and 1.
     * @param string|null $rootMargin Optional root margin.
     * @param string|null $when Visibility phase (e.g. enter/leave).
     */
    public function __construct(
        public readonly float $threshold = 0.0,
        public readonly ?string $rootMargin = null,
        public readonly ?string $when = 'enter'
    ) {
        if ($threshold < 0.0 || $threshold > 1.0) throw new InvalidArgumentException('VisibleTrigger: threshold 0..1');
        if ($rootMargin !== null && trim($rootMargin) === '') throw new InvalidArgumentException('VisibleTrigger: rootMargin non-empty if provided');
    }

    /**
     * Return trigger discriminator.
     *
     * @return string
     */
    public function type(): string { return 'visible'; }

    /**
     * Encode this trigger as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        $out = ['type'=>'visible','threshold'=>$this->threshold, 'when' => $this->when];
        if ($this->rootMargin !== null) $out['rootMargin'] = $this->rootMargin;
        return $out;
    }
}
