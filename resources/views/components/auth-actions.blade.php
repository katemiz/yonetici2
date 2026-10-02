@switch($attributes['action'])

    @case('login')
        <div class="columns">
            <div class="column">
                <p class="is-size-6 has-text-weight-light my-3">
                    <a href="/forgot-password">{{__('Forgot password?')}}</a>
                </p>
            </div>
        </div>
        @break

    @case('fpassword')
        <div class="columns">
            <div class="column is-half">
                <p class="is-size-6 has-text-weight-light my-3">
                    <a href="/login">{{__('Log In')}}</a>
                </p>
            </div>
        </div>

        @break

@endswitch
