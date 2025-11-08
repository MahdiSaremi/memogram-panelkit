<?php

namespace MemoGram\PanelKit\Broadcast;

use MemoGram\PanelKit\Jobs\BroadcastJob;
use function MemoGram\Handle\api;

class PvBroadcastLogger implements BroadcastLogger
{
    public function __construct(
        public $chatId,
        public $messageId = null,
    )
    {
    }

    public function created(BroadcastJob $job): void
    {
        $this->messageId = api()->sendMessage(
            chat_id: $this->chatId,
            text: $this->getText('created', $job),
            ignore: true,
        )?->message_id;
    }

    public function log(BroadcastJob $job): void
    {
        if ($this->messageId) {
            api()->editMessageText(
                text: $this->getText('progressing', $job),
                chat_id: $this->chatId,
                message_id: $this->messageId,
                ignore: true,
            );
        }
    }

    public function error(BroadcastJob $job, \Throwable $exception): void
    {
        api()->sendMessage(
            chat_id: $this->chatId,
            text: __('panelkit::tg-notification.log.error_template', ['message' => $exception->getMessage()]),
            ignore: true,
        );
    }

    public function completed(BroadcastJob $job): void
    {
        if ($this->messageId) {
            api()->editMessageText(
                text: $this->getText('completed', $job),
                chat_id: $this->chatId,
                message_id: $this->messageId,
                ignore: true,
            );
        }
    }

    protected function getText(string $status, BroadcastJob $job)
    {
        $title = __('panelkit::tg-notification.log.title.' . $status);
        $all = __('panelkit::tg-notification.log.counter.all', ['number' => $job->offset, 'all' => $job->cachedCount]);
        $success = __('panelkit::tg-notification.log.counter.success', ['number' => $job->successCount]);
        $failed = __('panelkit::tg-notification.log.counter.failed', ['number' => $job->failedCount]);
        $progress = $this->getProgress($job);

        return __(
            'panelkit::tg-notification.log.template',
            compact('title', 'all', 'success', 'failed', 'progress'),
        );
    }

    protected function getProgress(BroadcastJob $job)
    {
        // [                       ] 0%
        // [|||||||||||            ] 50%
        // [|||||||||||||||||||||||] 100%

        $a = round($job->offset / $job->cachedCount * 24);
        $b = 24 - $a;
        $percent = round($job->offset / $job->cachedCount * 100);

        return "[" . ($a ? str_repeat('|', $a) : '') . ($b ? str_repeat(' ', $b) : '') . "] $percent%";
    }
}
