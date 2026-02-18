<?php
declare(strict_types=1);

namespace Thorm\IR\Effect;

/**
 * Base contract for effect targets.
 *
 * Targets define where an effect is bound (self, window, document, selector).
 *
 * @group IR/Effect
 * @example
 * $target = new WindowTarget();
 */
interface EffectTarget extends \JsonSerializable
{
    /**
     * Encode target metadata for runtime.
     *
     * @return mixed Array payload or null for implicit self target.
     */
    public function jsonSerialize(): mixed;
}
