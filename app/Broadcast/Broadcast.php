<?php

namespace MemoGram\PanelKit\Broadcast;

use Closure;
use Illuminate\Database\Eloquent\Model;

class Broadcast
{
    /**
     * Make new builder
     *
     * @return BroadcastBuilder
     */
    public static function make()
    {
        return new BroadcastBuilder();
    }

    /**
     * Make new builder sending to all
     *
     * @return BroadcastBuilder
     */
    public static function toAll()
    {
        return static::make()->toAll();
    }

    /**
     * Make new builder sending to custom records or query
     *
     * @param iterable|Model|int|string|Closure $to
     * @return BroadcastBuilder
     */
    public static function to(iterable|Model|int|string|Closure $to)
    {
        return static::make()->to($to);
    }
}
