# Laravel VAAC: Verification and Activation

User account activation and verification by E-mail and Mobile.

## Table of Contents 
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Customization](#customization)
- [User profile / settings page](#user-profile-/-settings-page)   

## Installation

Require this package with composer.

```shell
composer require omadonex/vaac
```

Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the VaacServiceProvider. If you don't use auto-discovery, add the VaacServiceProvider to the providers array in config/app.php

```php
'providers' => [
    // [...]
    Omadonex\Vaac\Providers\VaacServiceProvider::class,
],
```

You may also register an alias for VaacService in config/app.php:

```php
'aliases' => [
    // [...]
    'Vaac' => Omadonex\Vaac\VaacService::class,
],
```

## Usage
You need to do some steps for successful using package/

#### 1. Run migrations
Vaac package contains a migration for verifies. Don't forget run it.
It assumes that you already have all necessary fields in `users` table ('email', 'phone'). Migration for updating `users` table is not included in package.
Also you can use your own fields in `users` table, they can be set in config file.

#### 2. Routes
Update your routes file with the command (it assumes that you registered alias for VaacService)
```php
Vaac::routes();
```
> If you want to use localization support for example the great package [mcamara/laravel-localization](https://github.com/mcamara/laravel-localization) then you must put this command into localization middleware group (all localization class names in this example are default as described in package):
```php
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localizationRedirect', 'localeSessionRedirect'],
], function () {
    ...
    Vaac::routes();
    ...
});
```

#### 3. VerifyAndActivate Trait
Your User model must use VerifyAndActivate Trait
```php
//User.php
...
use Omadonex\Vaac\Traits\VerifyAndActivate;
class User extends Authenticatable
{
    use Notifiable, ..., VerifyAndActivate;
    ...
}
```

#### 4. Views layout
Vaac Package provides default blade partial which checks verification status. Simple include it in your layout.
> partial requires two variables: **$signedIn (bool) and $user (User)** Please check that you are sharing them to views or pass from controllers or user auth() helper
  
```php
//app.blade.php
<body>
    ...
    @include('vaac::activate')
    or
    @include('vaac::activate', ['signedIn' => auth()->check(), 'user' => auth()->user()])
    ...
</body>    
```

> If you want to see status messages like 'Your account has been successfully activated.' or 'We have send you a new activation code, please check your phone.'
  don't forget include default block for retrieving session flash data. All status messages info stored in 'status' and 'status_message' variables. (blade partial for displaying flash session data is not included in package so define it yourself like in example below)

```php
//app.blade.php (Bootstrap example)
<body>
  ...
  @if (session('status'))
      <div class="alert alert-{{ session('status') }}">
          {{ session('status_message') }}
      </div>
  @endif
  @include('vaac::activate')
  ...
</body>    
```

#### 5. Init activation process
When a new user creates account you need to start activation process by sending notifications with activation instructions. 
You just need to call method `initActivation` of `VaacService` in a proper time.
For example, you have an event `AccountRegisterd` and a listener for this:
```php
class AccountRegisteredListener implements ShouldQueue
{
    ...
    public function handle(AccountRegistered $event)
    {
        Vaac::initActivation($event->User);        
        ...
    }
    ...
}
```

#### 6. RouteNotification for Nexmo
Cause we are using notifications by Mobile (ofc, if you set this method in config) we need to do some default settings for using sms channel for notifications.
Don't forget to set `routeNotificationForNexmo` method if you are using this provider for sms.
`VerifyAndActivate` trait contains a method `vaacPhoneForNotification`. This method returns a current mobile number of user for sending notification.

```php
//User.php
...
public function routeNotificationForNexmo()
{
    return $this->vaacPhoneForNotification();
}
...
```

## Configuration
Publishing config file:

```shell
php artisan vendor:publish --tag=config
```

Config file consists of three main sections `methods`, `email` and `phone`.
`methods` section is an array of available methods. You can use both of them simultaneously.
`email` and `phone` sections contains settings for activation:
- field - name of the field in `users` table that used for verification processes by described method.
- rule - name of the validation rule. This rule will be applied for checking data when user changes email of phone.
- token_length - length of generated verification token.
- freeze - time in seconds before a new verification attempt.
- attempts - count of attempts by method per day.
- notification_class - class name of your own notification class for method. Leave null if you want to use default classes provided by package.

Also you can specify a username field that uses in notifications for greetings.

## Customization
Vaac package contains translations, blade templates and pre-defined notifications. You can customize all of them. 
#### 1. Translations
Publishing translations:
```shell
php artisan vendor:publish --tag=translations
```

Translations for each language consists of two files `common` and `email`. 
common.php contains strings for status messages, parts of views and links.
email.php contains string for body of e-mail notification.

At this moment, package support only two locales: `en` and `ru`
 
#### 2. Blade templates
Publishing Blade templates:

```shell
php artisan vendor:publish --tag=views
```

- activate.blade.php - default partial that used for information about current verification status
- email.blade.php - info and link for resending verification by email.
- phone.blade.php - info, form for activation code and link for resending verification by phone.
 
#### 3. Notifications
Vaac package has a base class for generated notifications `VaacNotifcation`. It has two parameters `user` and `verify`. It enough for all purposes.
By default, Vaac package uses Nexmo for sendind sms notifications. You can define your own notification using another channel. For example, if you want to use SmscRu - create your own notification, update `routeNotificationFor` method in User model, and set `notification_class` in config file.

```php
//Notifications/PhoneNotificationForVaac.php
<?php
namespace App\Notifications;
use NotificationChannels\SmscRu\SmscRuChannel;
use NotificationChannels\SmscRu\SmscRuMessage;
use Omadonex\Vaac\Notifications\VaacNotification;
class PhoneNotificationForVaac extends VaacNotification
{
    public function via($notifiable)
    {
        return [SmscRuChannel::class];
    }

    public function toSmscRu($notifiable)
    {
        return SmscRuMessage::create($this->getSmsMessage());
    }
}
```
 
```php
//User.php
...
public function routeNotificationForSmscru()
{
    return $this->vaacPhoneForNotification();
}
...
```

```php
//vaac.php
...
'phone' => [
    ...
    'notification_class' => 'PhoneNotificationForVaac',
];
```

Same, you can do for E-mail notification, if you want to use E-mail Notification provided by package dont forget to set 'field_username' in config file.
 
## User profile / settings page
Most common user profile / setting page should give to user ability for changing E-mail and Mobile.
Vaac package provides easy way to do this.
##### 1. VerifyStatusData
VerifyAndActivate trait have a method `vaacVerifyStatusData`. This method returns     Getting actual status of verifying data pairs of method and (status, value).
Status contains current status of verification by method.
Value contains not approved new E-mail of Mobile depends on method or Null value if no need verification by this method.
You can check status of verifying and can request sending verification instructions again.

##### 2. Example of profile page
It just a simple example to show how you can made E-mail of Mobile changing process.
You can use routes for changing, also pre-defined info partials for displaying current status and ofc, for resending notifications.

> Note: this is just extra example that placed here only for demonstrating E-mail and Mobile changing process by using vaacVerifyStatusData method.      
 
```php
//profile.blade.php
@section('content')
    Profile page
    <div class="row">
        <div class="col-md-4">
            {{ $user->email }}
            @if ($user->isActivated())
                <form class="form-inline" method="post" action="{{ route('vaac.change', Vaac::METHOD_EMAIL) }}">
                    {{ csrf_field() }}
                    <input type="email" name="email">
                    <button type="submit">Change</button>
                </form>
            @endif
        </div>
        <div class="col-md-4">
            @if ($verifyStatusData['email']['status'] == Vaac::VERIFIED)
                <span class="label label-success">approved</span>
            @else
                @if ($verifyStatusData['email']['value'] == $user->email)
                    <span class="label label-warning">on approve</span>
                @else
                    email was changed ({{ $verifyStatusData['email']['value'] }})
                @endif
                @include('vaac::info.email')
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            {{ $user->phone }}
            @if ($user->isActivated())
                <form class="form-inline" method="post" action="{{ route('vaac.change', Vaac::METHOD_PHONE) }}">
                    {{ csrf_field() }}
                    <input type="text" name="phone">
                    <button type="submit">Change</button>
                </form>
            @endif
        </div>
        <div class="col-md-4">
            @if ($verifyStatusData['phone']['status'] == Vaac::VERIFIED)
                <span class="label label-success">approved</span>
            @else
                @if ($verifyStatusData['phone']['value'] == $user->phone)
                    <span class="label label-warning">on approve</span>
                @else
                    phone was changed ({{ $verifyStatusData['phone']['value'] }})
                    @include('vaac::info.phone')
                @endif
            @endif
        </div>
    </div>
@endsection
```