<?php
declare(strict_types=1);

namespace PhpJs\IR;

use JsonSerializable;

final class Atom implements JsonSerializable {
    private static int $nextId = 1;
    public readonly int $id;
    public mixed $initial;

    public function __construct(mixed $initial) {
        $this->id = self::$nextId++;
        $this->initial = $initial;
    }

    public function jsonSerialize(): mixed {
        return ['id' => $this->id, 'initial' => $this->initial];
    }
}
