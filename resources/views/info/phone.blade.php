<form class="form-inline" method="post" action="{{ \Omadonex\Vaac\VaacService::getVerifyPhoneUrl() }}">
    {{ csrf_field() }}
    <div class="form-group">
        @lang('vaac::common.resend.phone.info')
        <a class="alert-link" href="{{ route('vaac.resend', \Omadonex\Vaac\VaacService::METHOD_PHONE) }}">
            @lang('vaac::common.resend.phone.link')
        </a>
        <span style="margin-left: 1em;">
            @lang('vaac::common.resend.phone.otherwise')
        </span>
    </div>
    <div class="form-group" style="margin-left: .5em;">
        <div class="input-group input-group-sm">
            <input type="text" class="form-control" name="token" placeholder="@lang('vaac::common.resend.phone.placeholder')">
            <span class="input-group-btn">
                <button class="btn btn-default" type="submit">@lang('vaac::common.resend.phone.ok')</button>
            </span>
        </div>
    </div>
</form>