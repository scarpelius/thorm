<?php
declare(strict_types=1);
namespace PhpJs\IR\Effect;
final class DocumentTarget implements EffectTarget {
    public function jsonSerialize(): array { return ['type'=>'document']; }
}
