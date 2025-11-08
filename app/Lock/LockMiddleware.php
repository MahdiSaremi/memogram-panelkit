<?php

namespace MemoGram\PanelKit\Lock;

use MemoGram\Handle\Middleware\Middleware;
use function MemoGram\Handle\update;

class LockMiddleware implements Middleware
{
    protected ?LockCondition $condition;

    public function __construct(
        protected string          $group = 'main',
        null|LockCondition|string $condition = null,
    )
    {
        $this->condition = is_string($condition) ? new $condition : $condition;
    }

    public function handle(\Closure $next): mixed
    {
        if (!update() || !app(LockRequest::class)->openRequest($this->group)) {
            return $next();
        }

        return null;
    }
}