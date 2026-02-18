<?php
declare(strict_types=1);

namespace Thorm\IR\Effect;

/**
 * Base contract for effect triggers.
 *
 * Triggers define when an effect should execute.
 *
 * @group IR/Effect
 * @example
 * $trigger = new MountTrigger();
 */
interface EffectTrigger extends \JsonSerializable
{
    /**
     * Return trigger discriminator.
     *
     * @return string
     */
    public function type(): string;

    /**
     * Encode trigger metadata for runtime.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed;
}
