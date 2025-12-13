<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

/**
 * RouteNode
 *
 * Top-level switch that mounts exactly one view based on location.pathname.
 * - Compiles authoring table ['/path/:param' => Node] into [{pat,re,keys}] once in PHP.
 * - Provides ctx.route = { path, params, query } to the matched subtree.
 * - Disposes previous view scope on route change to avoid leaks.
 */
final class RouteNode extends Node implements \JsonSerializable {
     /** 
     * Precompiled patterns with their capture keys.
     * @var array<int, array{pat:string, re:string, keys:array<int,string>}>
     */
    public array $tableCompiled = [];

    /**
     * @param array<string, Node> $table     Map of path patterns to view Nodes (author order = match priority).
     * @param Node                $fallback  View to render when no pattern matches (404).
     * @param string              $base      Optional base path (default '/'), stripped before matching.
     */
    public function __construct(
        public array $table,
        public Node  $fallback,
        public string $base = '/', // optional, default '/'
    ) {
        foreach (array_keys($table) as $pat) {
            [$re, $keys] = self::compilePattern($pat);
            $this->tableCompiled[] = ['pat' => $pat, 're' => $re, 'keys' => $keys];
        }
    }

    /**
     * Compile a route pattern (e.g., "/auction/:id") into a regex and ordered param keys.
     *
     * @param  string $pat
     * @return array{0:string,1:array<int,string>} Tuple [regex, keys].
     */
    private static function compilePattern(string $pat): array {
        if ($pat !== '/' && str_ends_with($pat, '/')) $pat = rtrim($pat, '/');
        $parts = explode('/', $pat);
        $keys = [];
        $reParts = [];
        foreach ($parts as $seg) {
            if ($seg === '') { $reParts[] = ''; continue; }
            if ($seg[0] === ':') {
                $keys[] = substr($seg, 1);
                $reParts[] = '([^/]+)';
            } else {
                $reParts[] = preg_quote($seg, '/');
            }
        }
        $re = '/^' . implode('\/', $reParts) . '$/';
        return [$re, $keys];
    }

     /**
     * @return array{
     *   k: 'route',
     *   base: string,
     *   table: array<int, array{pat:string, re:string, keys:array<int,string>}>,
     *   views: array<int, mixed>,
     *   fallback: mixed
     * }
     * IR shape consumed by runtime router (one outlet, popstate listener, pushState updates).
     */
    public function jsonSerialize(): mixed {
        // Preserve author order between compiled table and views
        $views = array_values($this->table);

        return [
            'k'       => 'route',
            'base'    => $this->base,
            'table'   => $this->tableCompiled,
            'views'   => $views,
            'fallback'=> $this->fallback,
        ];
    }
}
