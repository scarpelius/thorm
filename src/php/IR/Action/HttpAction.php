<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

/**
 * Action that performs an HTTP request.
 *
 * @group IR/Action
 * @example
 * $action = new HttpAction('/api/ping', 'GET');
 */
final class HttpAction implements Action, AtomCollectable
{
    /**
     * Build an HTTP action.
     *
     * @param Expr|string $url Request URL.
     * @param string $method HTTP method.
     * @param Atom|int|null $to Target atom for response payload.
     * @param Atom|int|null $status Target atom for HTTP status.
     * @param array<string, mixed>|null $reqHeaders Request headers.
     * @param Atom|int|null $resHeaders Target atom for response headers.
     * @param Expr|int|float|string|bool|array|null $body Request body.
     * @param string $parse Parse mode.
     */
    public function __construct(
        public readonly Expr|string $url,
        public readonly string $method = 'GET',
        public readonly Atom|int|null $to = null,
        public readonly Atom|int|null $status = null,
        public readonly array|null $reqHeaders = null, // assoc array of scalar|Expr
        public readonly Atom|int|null $resHeaders = null,       // atom id to store response headers
        public readonly Expr|int|float|string|bool|array|null $body = null,
        public readonly string $parse = 'json'
    ) {}

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'http'; }

    /**
     * Collect atom dependencies referenced by this action.
     *
     * @param callable $collect Collector callback.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        if ($this->to instanceof Atom) $collect($this->to);
        if ($this->status instanceof Atom) $collect($this->status);
        if ($this->resHeaders instanceof Atom) $collect($this->resHeaders);

        if ($this->url instanceof Expr) $collect($this->url);
        if ($this->body instanceof Expr) $collect($this->body);

        if (is_array($this->reqHeaders)) {
            foreach ($this->reqHeaders as $v) {
                if ($v instanceof Expr) $collect($v);
            }
        }
    }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $url = $this->url instanceof \JsonSerializable ? $this->url->jsonSerialize() : $this->url;

        $out = [
            'k'      => $this->kind(),
            'url'    => $url,
            'method' => strtoupper($this->method),
            'parse'  => $this->parse,
        ];
        if ($this->to      !== null) $out['to']      = $this->to instanceof Atom ? $this->to->id : $this->to;
        if ($this->status  !== null) $out['status']  = $this->status instanceof Atom ? $this->status->id : $this->status;
        
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
            $out['resHeaders'] = $this->resHeaders instanceof Atom ? $this->resHeaders->id : $this->resHeaders;
        }

        if ($this->body instanceof \JsonSerializable) {
            $out['body'] = $this->body->jsonSerialize();
        } elseif ($this->body !== null) {
            $out['body'] = $this->body;
        }
        
        return $out;
    }
}
