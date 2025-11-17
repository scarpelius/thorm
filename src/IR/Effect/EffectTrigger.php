<?php
declare(strict_types=1);

namespace PhpJs\IR\Effect;

interface EffectTrigger extends \JsonSerializable
{
    /** "mount" | "watch" | "interval" | "timeout" | "visible" | "event" */
    public function type(): string;
    /** @return array */
    public function jsonSerialize(): mixed;
}
