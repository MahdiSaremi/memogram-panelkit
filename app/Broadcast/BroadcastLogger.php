<?php

namespace MemoGram\PanelKit\Broadcast;

use MemoGram\PanelKit\Jobs\BroadcastJob;

interface BroadcastLogger
{
    /**
     * Log creation event
     *
     * @param BroadcastJob $job
     * @return void
     */
    public function created(BroadcastJob $job): void;

    /**
     * Log the status for each partitions
     *
     * @param BroadcastJob $job
     * @return void
     */
    public function log(BroadcastJob $job): void;

    /**
     * Log the notifier errors (unexpected but possible)
     *
     * @param BroadcastJob $job
     * @param \Throwable $exception
     * @return void
     */
    public function error(BroadcastJob $job, \Throwable $exception): void;

    /**
     * Log the completed status
     *
     * @param BroadcastJob $job
     * @return void
     */
    public function completed(BroadcastJob $job): void;
}
