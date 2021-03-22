<style>
    .login-box {
        margin-top: -10rem;
        padding: 5px;
    }

    .login-card-body {
        padding: 1.5rem 1.8rem 1.6rem;
    }

    .card,
    .card-body {
        border-radius: .25rem;
        box-shadow: 0 5px 5px 0 rgb(0 0 0 / 25%);
        background: url("/img/bg-2.jpg") fixed;
        -webkit-transition: none;
        transition: none;
        background-size: 1666px 937px;
        background-position: -263px 0px;
    }

    .login-btn {
        padding-left: 2rem !important;
        ;
        padding-right: 1.5rem !important;
    }

    .animated {
        -webkit-animation-duration: 1s;
        animation-duration: 1s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .zoomIn {
        -webkit-animation-name: zoomIn;
        animation-name: zoomIn;
    }

    .content {
        overflow-x: hidden;
    }

    .form-group .control-label {
        text-align: left;
    }

    .login-page {
        background: url("/img/bg.jpg") center center no-repeat !important;
        background-size: 100%;
    }

    .form-label-group>label {
        color: white !important;
    }

    .vs-checkbox-con .vs-checkbox {
        border: 2px solid #ffffff !important;
    }

    .form-label-group>input:not(:focus):not(:placeholder-shown)~label,
    .form-label-group textarea:not(:focus):not(:placeholder-shown)~label {
        color: white !important;
    }

    .form-label-group>input:focus:not(:placeholder-shown)~label,
    .form-label-group>input:not(:active):not(:placeholder-shown)~label,
    .form-label-group textarea:focus:not(:placeholder-shown)~label,
    .form-label-group textarea:not(:active):not(:placeholder-shown)~label {
        color: white !important;
    }
</style>

<div class="login-page text-white">
    <div class="login-box">
        <div class="login-logo mb-2">
            <b>{{ config('admin.name') }}</b>
        </div>
        <div class="card animated zoomIn">
            <div class="card-body login-card-body shadow-100">
                <p class="login-box-msg mt-1 mb-1 text-white">{{ __('admin.welcome_back') }}</p>

                <form id="login-form" method="POST" action="{{ admin_url('auth/login') }}">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                    <fieldset class="form-label-group form-group position-relative has-icon-left">
                        <input type="text" class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}" name="username" placeholder="{{ trans('admin.username') }}" value="{{ old('username') }}" required autofocus>

                        <div class="form-control-position">
                            <i class="feather icon-user"></i>
                        </div>

                        <label for="email">{{ trans('admin.username') }}</label>

                        <div class="help-block with-errors"></div>
                        @if($errors->has('username'))
                        <span class="invalid-feedback text-danger" role="alert">
                            @foreach($errors->get('username') as $message)
                            <span class="control-label" for="inputError"><i class="feather icon-x-circle"></i> {{$message}}</span><br>
                            @endforeach
                        </span>
                        @endif
                    </fieldset>

                    <fieldset class="form-label-group form-group position-relative has-icon-left">
                        <input minlength="5" maxlength="20" id="password" type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" placeholder="{{ trans('admin.password') }}" required autocomplete="current-password">

                        <div class="form-control-position">
                            <i class="feather icon-lock"></i>
                        </div>
                        <label for="password">{{ trans('admin.password') }}</label>

                        <div class="help-block with-errors"></div>
                        @if($errors->has('password'))
                        <span class="invalid-feedback text-danger" role="alert">
                            @foreach($errors->get('password') as $message)
                            <span class="control-label" for="inputError"><i class="feather icon-x-circle"></i> {{$message}}</span><br>
                            @endforeach
                        </span>
                        @endif

                    </fieldset>
                    <div class="form-group d-flex justify-content-between align-items-center">
                        <div class="text-left">
                            @if(config('admin.auth.remember'))
                            <fieldset class="checkbox">
                                <div class="vs-checkbox-con vs-checkbox-primary text-white">
                                    <input id="remember" name="remember" value="1" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
                                    <span class="vs-checkbox">
                                        <span class="vs-checkbox--check">
                                            <i class="vs-icon feather icon-check"></i>
                                        </span>
                                    </span>
                                    <span> {{ trans('admin.remember_me') }}</span>
                                </div>
                            </fieldset>
                            @endif
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success float-right login-btn">

                        {{ __('admin.login') }}
                        &nbsp;
                        <i class="feather icon-arrow-right"></i>
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    Dcat.ready(function() {
        // ajax表单提交
        $('#login-form').form({
            validate: true,
        });
    });
</script>