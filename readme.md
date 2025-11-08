# Panel Kit

## Installation

```shell
composer require memogram/panelkit
```

### Publish

Optionally, you can publish the assets:

```shell
php artisan vendor:publish --tag="panelkit:config"
php artisan vendor:publish --tag="panelkit:lang"
```

## Broadcast
Broadcast is a service to notify the users by a message

### Ready To Use
Add these lines to show global sending method actions:

```php
return messageResponse()
    ->schema([
        [
            key("Forward", fn () => open([BroadcastForm::class, 'forwardForm'])),
            key("Message", fn () => open([BroadcastForm::class, 'messageForm'])),
        ]
    ]);
```


### Fast Use

Send to all users a message:

```php
Broadcast::toAll()
    ->message(['text' => 'Hello Everyone!'])
    ->log(update()->getChatId())
    ->notify();
```

### Logger
Logger logging the notifier status

Available builtin loggers:

```php
new PvBroadcastLogger(CHAT_ID)
```

Creating customize classes:

```php
class CustomLogger implements BroadcastLogger
{

    public function created(BroadcastJob $job) : void
    {
        // ...
    }
    
    public function log(BroadcastJob $job) : void
    {
        // ...
    }
    
    public function error(BroadcastJob $job, \Throwable $exception) : void
    {
        // ...
    }
    
    public function completed(BroadcastJob $job) : void
    {
        // ...
    }

}
```

Usage:

```php
Broadcast::toAll()
    ->send(['text' => 'Foo'])
    ->logger(new CustomLogger())
    ->notify();
```


## Lock

Lock system used to protect contents by favorite lock methods
like forcing channel joining

### Ready To Use

Middleware:

```php
withGlobalMiddleware(new LockMiddleware());
```

Use the section:

```php
return messageResponse()
    ->schema([
        [key("🔒 Locks", [LockResourceSection::class, 'main'])], // todo
    ]);
```

### Fast Use

Works with locks:

```php
$lock = Lock::push(...);  // To add a new lock
$lock->delete(); // To delete the lock
```

Using `useLock` hook:

```php
public function lockedContent()
{
    $lock = app(LockRequest::class)->useLock();
    
    yield from $lock();
    
    yield "Actual content";
}
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
    public function show(): bool
    {
        return User::$current->id % 2 == 1;
    }
}
```

Set globally condition in config:

```php
    'lock' => [
        'condition' => UserIsOddCondition::class,
    ],
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
Broadcast::make()
    ->aim(new TgNotBannedAim())
    ->send(['text' => 'Hi'])
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
    public function notify(Model $record): bool
    {
        evnetHandler()-> // todo
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
Broadcast::toAll()
    ->notifier(new TgHomeSectionNotifier())
    ->notify();
```

