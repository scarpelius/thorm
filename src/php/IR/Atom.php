<?php
declare(strict_types=1);

namespace Thorm\IR;

use JsonSerializable;

final class Atom implements JsonSerializable {
    private static int $nextId = 1;
    public readonly int $id;
    public mixed $initial;
    /** @var array<string, mixed> */
    public array $meta;

    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(mixed $initial, array $meta = []) {
        $this->id = self::$nextId++;
        $this->initial = $initial;
        $this->meta = $meta;
    }

    public function jsonSerialize(): mixed {
        $out = ['id' => $this->id, 'initial' => $this->initial];
        if (!empty($this->meta)) {
            $out['meta'] = $this->meta;
        }
        return $out;
    }
}
