# Panel Kit

## Installation

```shell
composer require mmb/panelkit
```

### Publish

Optionally, you can publish the assets:

```shell
php artisan vendor:publish --tag="panelkit:config"
php artisan vendor:publish --tag="panelkit:lang"
```

## Every
Every is a service to notify the users by a message

### Ready To Use
Add these lines to show global sending method actions:

```php
$menu->schema([
    [
        $menu->key("Forward", fn () => EveryForwardForm::make()->request()),
        $menu->key("Message", fn () => EveryMessageForm::make()->request()),
    ]
])
```


### Fast Use

Send to all users a message:

```php
Every::toAll()
    ->send(['text' => 'Hello Everyone!'])
    ->log($this->update->getChat()->id)
    ->notify();
```

Send to specific users a dynamic message:

```php
Every::to(fn () => BotUser::where('ban', false)->orderBy('created_at'))
    ->send()
    ->message(fn (BotUser $user) => ['text' => "Hello {$user->name}!"])
    ->log($this->update->getChat()->id)
    ->notify();
```

### Logger
Logger logging the notifier status

Available builtin letters:

```php
new PvEveryLogger(CHAT_ID)
```

Creating customize classes:

```php
class CustomLogger implements EveryLogger
{

    public function created(EveryJob $job) : void
    {
        // ...
    }
    
    public function log(EveryJob $job) : void
    {
        // ...
    }
    
    public function error(EveryJob $job, \Throwable $exception) : void
    {
        // ...
    }
    
    public function completed(EveryJob $job) : void
    {
        // ...
    }

}
```

Usage:

```php
Every::toAll()
    ->send(['text' => 'Foo'])
    ->logger(new CustomLogger())
    ->notify();
```


## Lock

Lock system used to protect contents by favorite lock methods
like forcing channel joining

### Ready To Use

Add handlers:

```php
$handler->callback(LockMiddleAction::class),
LockRequest::for($this->context, 'main'), // For each groups
```

Use the section:

```php
$menu->schema([
    [$menu->keyFor("🔒 Locks", LockResourceSection::class)],
]);
```

### Fast Use

Works with locks:

```php
$lock = Lock::add(...);  // To add a new lock
$lock->delete(); // To delete the lock
```

Adding the `required` to custom part of code:

```php
LockRequest::for($this->context, 'main')->required();
```


### Fixed Channels

Change the config:

```php
    'lock' => [
        'fixed' => [
            [
                'chat_id' => -123455678,
                'title' => 'Join',
                'url' => 'https://t.me/Link',
                'group' => 'main',
            ],
        ],
    ],
```


### Lock Condition

```php
class UserIsOddCondition implements LockCondition
{
    public function show() : bool
    {
        return BotUser::current()->id % 2 == 1;
    }
}
```

Set globally condition in config:

```php
    'lock' => [
        'condition' => UserIsOddCondition::class,
    ],
```

Or set in specific request:

```php
LockRequest::for($this->context, 'main')->withCondition(UserIsOddCondition::class)
```



### Customize

Custom the alert dialog:

```php
class PostLockRequest extends LockRequest
{

    #[Find]
    public Post $post;
    
    public function withPost(Post $post)
    {
        $this->post = $post;
        return $this;
    }

    #[FixedDialog('lock:{group:slug}:{post:slug}')]
    public function mainDialog(Dialog $dialog)
    {
        parent::mainDialog($dialog);
        
        $dialog
            ->on('submit', function () use ($dialog)
            {
                if ($this->locks)
                {
                    $this->tell(__('panelkit::lock.submit_invalid'), alert: true);
                    $dialog->reload();
                }
                else
                {
                    $this->update->getMessage()?->delete(ignore: true);
                    PostSection::invokes('main', $this->post);
                }
            }
            );
    }
}
```

Usage:

```php
PostLockRequest::for($this->context, 'main')->withPost($myPost)->required();
```


## Targets
Targets is a collection of tools to customize the actions

### Aim
Aim set the target query and records

Available builtin aims:

```php
new TgAllAim()
new TgCustomAim(new SerializableClosure(function () {...}))
```

Creating customize classes:

```php
class TgNotBannedAim implements TgAim
{

    public function getQuery() : Builder
    {
        return BotUser::whereIsNull('ban_until')->orderBy('created_at');
    }

}
```

> We trust on `orderBy('created_at')` to sort the records by a stable
> order to prevent double sending or not sending to some users.

Usage:

```php
Every::make()
    ->aim(new TgNotBannedAim())
    ->send(['text' => 'Hi'])
    ->notify();
```


### Letter
Letter set the message value

Available builtin letters:

```php
new TgFixedLetter(['text' => 'Hello Mmb!'])
new TgEmptyLetter()
new TgCustomLetter(new SerializableClosure(function () {...}))
```

Creating customize classes:

```php
class TgWelcomeLetter implements TgLetter
{

    public function getLetter(Model $record) : array
    {
        return [
            'text' => "Welcome {$record->name}!",
        ];
    }

}
```

Usage:

```php
Every::toAll()
    ->send()
    ->letter(new TgWelcomeLetter())
    ->notify();
```


### Notifier
Notifier set the sending method

Available builtin notifiers:

```php
new TgMessageNotifier()
new TgForwardNotifier()
new TgCustomNotifier(new SerializableClosure(function () {...}))
```

Creating customize classes:

```php
class TgHomeSectionNotifier implements TgNotifier
{

    public function notify(Model $record, array $message) : bool
    {
        return (bool) pov()
            ->user($record)
            ->catch()
            ->run(
                fn () => HomeSection::invokes('main')
            );
    }

}
```

Usage:

```php
Every::toAll()
    ->notifier(new TgHomeSectionNotifier())
    ->notify();
```

