<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

/**
 * Effect target bound to `window`.
 *
 * @group IR/Effect
 * @example
 * $target = new WindowTarget();
 */
final class WindowTarget implements EffectTarget {
    /**
     * Encode this target as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): array { return ['type'=>'window']; }
}
