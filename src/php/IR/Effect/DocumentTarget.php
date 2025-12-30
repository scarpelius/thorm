<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;
final class DocumentTarget implements EffectTarget {
    public function jsonSerialize(): array { return ['type'=>'document']; }
}
