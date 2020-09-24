@extends('layouts.app')

@section('content')
<div class="container">
    <div class="login">
            <div class="card">
                <div class="card-header">{{ __('web.Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group">
                            <label for="first_name">{{ __('web.first_name') }}</label>

                                <input id="first_name" type="text" class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}" name="first_name" value="{{ old('first_name') }}" required autofocus>

                                @if ($errors->has('first_name'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('first_name') }}</strong>
                                    </span>
                                @endif
                        </div>

                        <div class="form-group">
                            <label for="last_name">{{ __('web.last_name') }}</label>

                            <input id="last_name" type="text" class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}" name="last_name" value="{{ old('last_name') }}" required autofocus>

                            @if ($errors->has('last_name'))
                                <span class="invalid-feedback">
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="email">{{ __('web.E-Mail Address') }}</label>

                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                        </div>

                        <div class="form-group">
                            <label for="password">{{ __('web.Password') }}</label>

                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                        </div>

                        <div class="form-group">
                            <label for="password-confirm">{{ __('web.Confirm Password') }}</label>

                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

                        </div>

                        <div class="form-group" style="margin-top: 1.5rem">
                            @if(auth()->guest())
                                <label>Som človek  3+2 = </label>
                                <input type="number" name="iamHuman" placeholder="Zadajte číslo 5" style="opacity: .85;color: black; width: 50%" required>
                            @endif
                        </div>



                        <div class="form-group">
                            <div class="level">
                                <a title="Registrácia cez Facebook">
                                    Registrácia cez FB
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    {{ __('web.Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</div>
@endsection
