<?php

namespace MemoGram\PanelKit\Broadcast;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Traits\Conditionable;
use Laravel\SerializableClosure\SerializableClosure;
use MemoGram\Api\Types\Chat;
use MemoGram\Api\Types\Message;
use MemoGram\Api\Types\Update;
use MemoGram\PanelKit\Jobs\BroadcastJob;
use MemoGram\PanelKit\PanelKit;
use MemoGram\PanelKit\Targets\Aim\TgAim;
use MemoGram\PanelKit\Targets\Aim\TgAllAim;
use MemoGram\PanelKit\Targets\Aim\TgCustomAim;
use MemoGram\PanelKit\Targets\Notifier\TgCustomNotifier;
use MemoGram\PanelKit\Targets\Notifier\TgForwardNotifier;
use MemoGram\PanelKit\Targets\Notifier\TgMessageNotifier;
use MemoGram\PanelKit\Targets\Notifier\TgNotifier;
use MemoGram\Support\MessageContent;

class BroadcastBuilder
{
    use Conditionable;

    protected TgNotifier $notifier;
    protected TgAim $aim;
    protected BroadcastLogger $logger;

    /**
     * Set notifier instance
     *
     * @param TgNotifier|Closure $notifier
     * @return $this
     */
    public function notifier(TgNotifier|Closure $notifier)
    {
        if ($notifier instanceof Closure) {
            $notifier = new TgCustomNotifier($notifier);
        }

        $this->notifier = $notifier;
        return $this;
    }

    /**
     * Set aim instance
     *
     * @param TgAim $aim
     * @return $this
     */
    public function aim(TgAim $aim)
    {
        $this->aim = $aim;
        return $this;
    }

    /**
     * Set logger instance
     *
     * @param BroadcastLogger $logger
     * @return $this
     */
    public function logger(BroadcastLogger $logger)
    {
        $this->logger = $logger;
        return $this;
    }


    /**
     * Set aim to all records
     *
     * @return $this
     */
    public function toAll()
    {
        return $this->aim(new TgAllAim());
    }

    /**
     * Set target to custom records or query
     *
     * @param iterable|Model|int|string|Closure $to
     * @return $this
     */
    public function to(iterable|Model|int|string|Closure $to)
    {
        if (is_string($to) || is_int($to) || $to instanceof Model) {
            $to = [$to];
        }

        if (is_iterable($to)) {
            $class = null;
            $ids = [];
            foreach ($to as $record) {
                if ($record instanceof Model) {
                    if ($class === null) {
                        $class = get_class($record);
                    } elseif ($class != get_class($record)) {
                        throw new \InvalidArgumentException("Can't parse multiple model types");
                    }

                    $ids[] = $record->getKey();
                } else {
                    $ids[] = $record;
                }
            }

            $to = function () use ($class, $ids) {
                return ($class ?? PanelKit::getUserClass())::whereIn($ids)->orderBy('created_at');
            };
        }

        return $this->aim(new TgCustomAim(new SerializableClosure($to)));
    }


    /**
     * Set the message content
     *
     * @param MessageContent $content
     * @return $this
     */
    public function message(MessageContent $content)
    {
        return $this->notifier(new TgMessageNotifier($content));
    }

    /**
     * Set the notifier to forward mode
     *
     * @param $chatId
     * @param $messageId
     * @return BroadcastBuilder
     */
    public function forward($chatId, $messageId)
    {
        return $this->notifier(new TgForwardNotifier($chatId, $messageId));
    }


    /**
     * Set logging to a chat
     *
     * @param string|int|Chat|Message|Update $chatId
     * @return $this
     */
    public function log(string|int|Chat|Message|Update $chatId)
    {
        $chatId = match (true) {
            $chatId instanceof Update => $chatId->getChatId(),
            $chatId instanceof Message => $chatId->chat->id,
            $chatId instanceof Chat => $chatId->id,
            default => $chatId,
        };

        return $this->logger(new PvBroadcastLogger($chatId));
    }


    /**
     * Dispatch the notification
     *
     * @return PendingDispatch
     */
    public function notify()
    {
        return dispatch(new BroadcastJob(
            aim: $this->aim ?? new TgAllAim(),
            notifier: $this->notifier ?? throw new \InvalidArgumentException("Notifier is not set."),
            logger: $this->logger ?? null,
        ));
    }
}
