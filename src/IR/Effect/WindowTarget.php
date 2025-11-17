<?php
declare(strict_types=1);
namespace PhpJs\IR\Effect;
final class WindowTarget implements EffectTarget {
    public function jsonSerialize(): array { return ['type'=>'window']; }
}
