<?php

namespace MemoGram\PanelKit\Lock;

use MemoGram\Handle\Middleware\Middleware;

class LockMiddleware implements Middleware
{
    protected ?LockCondition $condition;

    public function __construct(
        null|LockCondition|string $condition = null,
    )
    {
        $this->condition = is_string($condition) ? new $condition : $condition;
    }

    public function handle(\Closure $next): mixed
    {
        // TODO: Implement handle() method.
    }
}