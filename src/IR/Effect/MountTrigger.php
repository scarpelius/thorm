<?php
declare(strict_types=1);
namespace PhpJs\IR\Effect;

final class MountTrigger implements EffectTrigger {
    public function type(): string { return 'mount'; }
    public function jsonSerialize(): array { return ['type' => 'mount']; }
}
