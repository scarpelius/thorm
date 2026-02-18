<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

use InvalidArgumentException;

/**
 * Effect trigger fired on a fixed interval.
 *
 * @group IR/Effect
 * @example
 * $trigger = new IntervalTrigger(1000);
 */
final class IntervalTrigger implements EffectTrigger {
    /**
     * Build an interval trigger.
     *
     * @param int $ms Interval in milliseconds.
     */
    public function __construct(public readonly int $ms) {
        if ($ms <= 0) throw new InvalidArgumentException('IntervalTrigger: ms must be > 0.');
    }

    /**
     * Return trigger discriminator.
     *
     * @return string
     */
    public function type(): string { return 'interval'; }

    /**
     * Encode this trigger as runtime IR payload.
     *
     * @return array<string, int|string>
     */
    public function jsonSerialize(): array { return ['type'=>'interval','ms'=>$this->ms]; }
}
