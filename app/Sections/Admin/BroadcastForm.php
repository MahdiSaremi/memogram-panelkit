<?php

namespace MemoGram\PanelKit\Sections\Admin;

use MemoGram\PanelKit\Broadcast\Broadcast;
use MemoGram\PanelKit\PanelKit;
use function MemoGram\Handle\areaRegistry;
use function MemoGram\Handle\update;
use function MemoGram\Hooks\open;
use function MemoGram\Hooks\useForm;

class BroadcastForm
{
    public function forwardForm()
    {
        $form = useForm()->withCancel(fn() => $this->back());

        if ($form->missed('message')) {
            yield $form->prompt('message')
                ->rules(['message'])
                ->value(fn() => update()->message->message_id)
                ->response(__('panelkit::tg-notification.request_forward_message'));
        } else {
            Broadcast::toAll()
                ->forward(update()->getChatId(), $form->get('message'))
                ->log(update()->getChatId())
                ->notify();
            $this->back();
        }
    }

    public function messageForm()
    {
        $form = useForm()->withCancel(fn() => $this->back());

        if ($form->missed('message')) {
            yield $form->prompt('message')
                ->rules(['content'])
                ->value(fn() => update()->message->toContent())
                ->response(__('panelkit::tg-notification.request_send_message'));
        } else {
            Broadcast::toAll()
                ->message($form->get('message'))
                ->log(update()->getChatId())
                ->notify();
            $this->back();
        }
    }

    protected function back(): void
    {
        if ($back = areaRegistry()->getBackForClass(static::class)) {
            open($back);
        }
    }
}
