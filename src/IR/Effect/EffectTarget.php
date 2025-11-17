<?php
declare(strict_types=1);

namespace PhpJs\IR\Effect;

interface EffectTarget extends \JsonSerializable
{
    /** return array or null (null => implicit self target) */
    public function jsonSerialize(): mixed;
}
