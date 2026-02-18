<?php
declare(strict_types=1);

namespace Thorm\IR\Effect;

use InvalidArgumentException;
use Thorm\IR\Expr\Expr;

/**
 * Effect trigger for Server-Sent Events streams.
 *
 * @group IR/Effect
 * @example
 * $trigger = new SseTrigger('/events', 'message', 'json', false, null);
 */
final class SseTrigger implements EffectTrigger
{
    /**
     * Build an SSE trigger.
     *
     * @param Expr|string $url SSE endpoint URL.
     * @param string $event SSE event name.
     * @param string $parse Payload parsing mode: json|text|raw.
     * @param bool $withCredentials Whether EventSource should use credentials.
     * @param Expr|int|null $sinceId Optional replay id.
     */
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

    /**
     * Return trigger discriminator.
     *
     * @return string
     */
    public function type(): string { return 'sse'; }

    /**
     * Encode this trigger as runtime IR payload.
     *
     * @return array<string, mixed>
     */
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
