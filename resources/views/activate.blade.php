@if ($signedIn && !$user->isActivated())
    <div class="alert alert-warning" role="alert">
        <div><strong>@lang('vaac::common.verify.need')</strong></div>

        @if (in_array(\Omadonex\Vaac\VaacService::METHOD_PHONE, config('vaac.methods')) && $user->vaacGetFieldValue(\Omadonex\Vaac\VaacService::METHOD_PHONE))
            @include('vaac::info.phone')
        @endif

        @if (in_array(\Omadonex\Vaac\VaacService::METHOD_EMAIL, config('vaac.methods')) && $user->vaacGetFieldValue(\Omadonex\Vaac\VaacService::METHOD_EMAIL))
            @include('vaac::info.email')
        @endif
    </div>
@endif