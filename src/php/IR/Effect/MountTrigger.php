<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

/**
 * Effect trigger fired on mount.
 *
 * @group IR/Effect
 * @example
 * $trigger = new MountTrigger();
 */
final class MountTrigger implements EffectTrigger {
    /**
     * Return trigger discriminator.
     *
     * @return string
     */
    public function type(): string { return 'mount'; }

    /**
     * Encode this trigger as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): array { return ['type' => 'mount']; }
}
