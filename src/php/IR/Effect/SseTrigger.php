<?php
declare(strict_types=1);

namespace Thorm\IR\Effect;

use InvalidArgumentException;
use Thorm\IR\Expr\Expr;

final class SseTrigger implements EffectTrigger
{
    public function __construct(
        public readonly Expr|string $url,
        public readonly string $event = 'message',
        public readonly string $parse = 'json',          // 'json'|'text'|'raw'
        public readonly bool $withCredentials = false,
        public readonly Expr|int|null $sinceId = null    // optional ?sinceId=...
    ) {
        if (trim($this->event) === '') throw new InvalidArgumentException('SseTrigger: event cannot be empty');
        if (!in_array($this->parse, ['json','text','raw'], true)) throw new InvalidArgumentException('SseTrigger: parse must be json|text|raw');
    }

    public function type(): string { return 'sse'; }

    public function jsonSerialize(): array
    {
        $url = $this->url instanceof \JsonSerializable ? $this->url->jsonSerialize() : $this->url;

        $out = [
            'type' => 'sse',
            'url'  => $url,
            'event'=> $this->event,
            'parse'=> $this->parse,
            'withCredentials' => $this->withCredentials,
        ];

        if ($this->sinceId !== null) {
            $out['sinceId'] = $this->sinceId instanceof \JsonSerializable ? $this->sinceId->jsonSerialize() : $this->sinceId;
        }

        return $out;
    }
}
