<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;
final class WindowTarget implements EffectTarget {
    public function jsonSerialize(): array { return ['type'=>'window']; }
}
