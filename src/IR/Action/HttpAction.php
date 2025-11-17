<?php
declare(strict_types=1);

namespace PhpJs\IR\Action;

use PhpJs\IR\Expr\Expr;

final class HttpAction implements Action
{
    public function __construct(
        public readonly Expr|string $url,
        public readonly string $method = 'GET',
        public readonly ?int $to = null,
        public readonly ?int $status = null,
        public readonly array|null $reqHeaders = null, // assoc array of scalar|Expr
        public readonly ?int $resHeaders = null,       // atom id to store response headers
        public readonly Expr|int|float|string|bool|array|null $body = null,
        public readonly string $parse = 'json'
    ) {}

    public function kind(): string { return 'http'; }

    public function jsonSerialize(): array
    {
        $url = $this->url instanceof \JsonSerializable ? $this->url->jsonSerialize() : $this->url;

        $out = [
            'k'      => $this->kind(),
            'url'    => $url,
            'method' => strtoupper($this->method),
            'parse'  => $this->parse,
        ];
        if ($this->to      !== null) $out['to']      = $this->to;
        if ($this->status  !== null) $out['status']  = $this->status;
        
        // Request headers
        if (is_array($this->reqHeaders)) {
            // Serialize values that are Expr
            $hdrs = [];
            foreach ($this->reqHeaders as $k => $v) {
                $hdrs[$k] = $v instanceof \JsonSerializable ? $v->jsonSerialize() : $v;
            }
            $out['reqHeaders'] = $hdrs;
        }

        // Response headers atom
        if ($this->resHeaders !== null) {
            $out['resHeaders'] = $this->resHeaders;
        }

        if ($this->body instanceof \JsonSerializable) {
            $out['body'] = $this->body->jsonSerialize();
        } elseif ($this->body !== null) {
            $out['body'] = $this->body;
        }
        
        return $out;
    }
}
