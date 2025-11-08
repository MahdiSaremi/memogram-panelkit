<?php

namespace MemoGram\PanelKit\Sections\Admin\Lock;

use Illuminate\Contracts\Database\Query\Builder;
use MemoGram\PanelKit\Lock\Lock;
use MemoGram\PanelKit\Models\LockRequire;
use Mmb\Action\Filter\Filter;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\Input;
use Mmb\Action\Section\Resource\ResourceCreateModule;
use Mmb\Action\Section\Resource\ResourceDeleteModule;
use Mmb\Action\Section\Resource\ResourceEditModule;
use Mmb\Action\Section\Resource\ResourceInfoModule;
use Mmb\Action\Section\Resource\ResourceListModule;
use Mmb\Action\Section\Resource\ResourceOrderModule;
use Mmb\Action\Section\Resource\ResourceSimpleFilterModule;
use Mmb\Action\Section\ResourceMaker;
use Mmb\Action\Section\ResourceSection;
use function MemoGram\PanelKit\Mmb\Sections\Admin\Lock\bot;

class LockResourceSection extends ResourceSection
{

    protected $for = LockRequire::class;

    public function resource(ResourceMaker $maker)
    {
        $this->list($maker->list());

        $this->info($maker->info());
    }

    public function list(ResourceListModule $module)
    {
        $module
            ->message(__('panelkit::lock.resource.list'))
            ->label(fn(LockRequire $record) => "🔒 {$record->title} #{$record->id}")
            ->when(count(config('panelkit.lock.groups')) > 1)
            ->simpleFilter('group',
                function (ResourceSimpleFilterModule $module) {
                    $module
                        ->keyLabel(fn($keyLabel) => __('panelkit::lock.resource.toggle_group', ['group' => $keyLabel]));

                    $i = 0;
                    foreach (config('panelkit.lock.groups') as $group => $text) {
                        $module->add(__($text), fn(Builder $query) => $query->where('group', $group), $i++ == 0);
                    }
                },
                x: 130,
            )
            ->creatable($this->create(...))
            ->orderable($this->order(...));
    }

    public function order(ResourceOrderModule $module)
    {
        $module
            ->toggle();
    }

    public function create(ResourceCreateModule $module)
    {
        $this->sharedFormPrefix($module);

        $module
            ->input('is_public', fn(Input $input) => $input->if(false))
            ->input('chat_id',
                fn(Input $input, Form $form) => $input
                    ->filter(fn(Filter $filter) => $filter
                        ->textSingleLine()
                        ->or()
                        ->shouldForwardFromChannel(),
                    )
                    ->prompt(__('panelkit::lock.resource.form_select'))
                    ->filled(function () use ($input, $form) {
                        if (is_string($input->value)) {
                            if (preg_match('/^@([a-zA-Z0-9_]+)$/', $input->value)) {
                                if (!$channel = bot()->getChat(chat: $input->value, ignore: true)) {
                                    $form->error(__('panelkit::lock.resource.form_select_admin_error'));
                                }
                            } elseif (preg_match('/^-\d+$/', $input->value)) {
                                if (!$channel = bot()->getChat(chat: $input->value, ignore: true)) {
                                    $form->error(__('panelkit::lock.resource.form_select_admin_error'));
                                }
                            } else {
                                $form->error(__('panelkit::lock.resource.form_select_text_error'));
                            }
                        } else {
                            $channel = $this->update->getMessage()->forwardFromChat;
                        }

                        $input->value = $channel->id;
                        if ($channel->username) {
                            $form->is_public = true;
                            $form->suggested_url = "https://t.me/{$channel->username}";
                        } else {
                            $form->is_public = false;
                        }
                        $form->suggested_title = $channel->title;
                    }),
            );

        $this->sharedFormSuffix($module);

        $module
            ->creating(function (array $attributes) {
                return Lock::push(...$attributes);
            });
    }

    public function info(ResourceInfoModule $module)
    {
        $module
            ->message(fn(LockRequire $record) => [
                'text' => __('panelkit::lock.resource.info'),
            ])
            ->editable($this->edit(...))
            ->editableSingle(__('panelkit::lock.resource.info_group'), 'group', left: fn($value, $record) => "[ " . $record->group_label . " ]")
            ->editableSingle(__('panelkit::lock.resource.info_url'), 'url', left: fn($value, $record) => "[ " . $record->url_label . " ]")
            ->editableSingle(__('panelkit::lock.resource.info_title'), 'title', left: fn($value, $record) => "[ " . $record->title_label . " ]")
            ->editableSingle(__('panelkit::lock.resource.info_expire'), 'expire_at', left: fn($value, $record) => "[ " . $record->expire_at_label . " ]")
            ->editableSingle(__('panelkit::lock.resource.info_cache'), 'cache_pass', left: fn($value, $record) => "[ " . $record->cache_pass_label . " ]")
            ->editableSingle(__('panelkit::lock.resource.info_member_limit'), 'member_limit_until', left: fn($value, $record) => "[ " . $record->member_limit_until_label . " ]")
            ->deletable($this->delete(...));
    }

    public function edit(ResourceEditModule $module)
    {
        $this->sharedFormPrefix($module);

        $module
            // TODO : Date Picker
            ->input('expire_at',
                fn(Input $input) => $input->if(false, null),
            )
            ->input('cache_pass',
                fn(Input $input) => $input
                    ->prompt(__('panelkit::lock.resource.form_cache'))
                    ->skipKey()
                    ->unsignedInt(),
            )
            ->input('member_limit_until',
                fn(Input $input) => $input
                    ->prompt(__('panelkit::lock.resource.form_member_limit'))
                    ->skipKey()
                    ->unsignedInt(),
            );

        $this->sharedFormSuffix($module);
    }

    public function delete(ResourceDeleteModule $module)
    {
        $module// ->
        ;
    }

    public function sharedFormPrefix(ResourceCreateModule|ResourceEditModule $module)
    {
        $groups = config('panelkit.lock.groups');

        $module
            ->input('group',
                fn(Input $input) => $input
                    ->if(count($groups) > 1, $groups ? array_key_first($groups) : 'main')
                    ->prompt(__('panelkit::lock.resource.form_group'))
                    ->onlyOptions()
                    ->options(function () use ($groups, $input) {
                        foreach ($groups as $group => $text) {
                            yield [$input->key(__($text), $group)];
                        }
                    }),
            );
    }

    public function sharedFormSuffix(ResourceCreateModule|ResourceEditModule $module)
    {
        $module
            ->input('url',
                fn(Input $input, Form $form) => $input
                    ->jumpFilled()
                    ->prompt(__('panelkit::lock.resource.form_url'))
                    ->textSingleLine()
                    ->regex('/^(https?:\/\/)?(t\.me\/|telegram\.me\/)(.*)$/i', 3, __('panelkit::lock.resource.form_url_error'))
                    ->filled(fn() => $input->value = "https://t.me/" . $input->value)
                    ->when($form->suggested_url)
                    ->schema([
                        [$input->key($form->suggested_url, $form->suggested_url)],
                    ]),
            )
            ->textSingleLine('title', __('panelkit::lock.resource.form_title'),
                init: fn(Input $input, Form $form) => $input
                    ->when($form->suggested_title)
                    ->schema([
                        [$input->key($form->suggested_title, $form->suggested_title)],
                    ]),
            );
    }

}
