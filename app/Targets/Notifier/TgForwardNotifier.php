<?php

namespace MemoGram\PanelKit\Targets\Notifier;

use Illuminate\Database\Eloquent\Model;
use MemoGram\Support\Contracts\HasChatId;
use function MemoGram\Handle\api;

class TgForwardNotifier implements TgNotifier
{
    public function __construct(
        public $chatId,
        public $messageId,
    )
    {
    }

    public function notify(Model $record): bool
    {
        return (bool)api()->forwardMessage(
            chat_id: $record instanceof HasChatId ? $record->getChatId() : $record->id,
            from_chat_id: $this->chatId,
            message_id: $this->messageId,
            ignore: true,
        );
    }
}
