<?php

namespace MemoGram\PanelKit\Targets\Notifier;

use Illuminate\Database\Eloquent\Model;
use MemoGram\Support\Contracts\HasChatId;
use MemoGram\Support\MessageContent;
use function MemoGram\Handle\api;

class TgMessageNotifier implements TgNotifier
{
    public function __construct(
        protected MessageContent $content,
    )
    {
    }

    public function notify(Model $record): bool
    {
        return (bool)$this->content->send(
            api(),
            chat_id: $record instanceof HasChatId ? $record->getChatId() : $record->id,
            ignore: true,
        );
    }
}
