<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

use InvalidArgumentException;

/**
 * Effect trigger fired once after a delay.
 *
 * @group IR/Effect
 * @example
 * $trigger = new TimeoutTrigger(250);
 */
final class TimeoutTrigger implements EffectTrigger {
    /**
     * Build a timeout trigger.
     *
     * @param int $ms Delay in milliseconds.
     */
    public function __construct(public readonly int $ms) {
        if ($ms <= 0) throw new InvalidArgumentException('TimeoutTrigger: ms must be > 0.');
    }

    /**
     * Return trigger discriminator.
     *
     * @return string
     */
    public function type(): string { return 'timeout'; }

    /**
     * Encode this trigger as runtime IR payload.
     *
     * @return array<string, int|string>
     */
    public function jsonSerialize(): array { return ['type'=>'timeout','ms'=>$this->ms]; }
}
