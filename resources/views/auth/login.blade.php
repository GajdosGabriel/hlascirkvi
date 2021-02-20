@extends('layouts.app')

@section('content')
<div class="container min-h-screen">

    <login-card></login-card>
    {{--<div class="login">--}}
            {{--<div class="card">--}}
                {{--<div class="card-header">{{ __('Login') }}</div>--}}

                {{--<div class="card-body">--}}
                    {{--<form method="POST" action="{{ route('login') }}">--}}
                        {{--@csrf--}}

                        {{--<div class="form-group">--}}
                            {{--<label for="email">{{ __('web.E-Mail Address') }}</label>--}}

                                {{--<input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>--}}

                                {{--@if ($errors->has('email'))--}}
                                    {{--<span class="invalid-feedback">--}}
                                        {{--<span>{{ $errors->first('email') }}</span>--}}
                                    {{--</span>--}}
                                {{--@endif--}}

                        {{--</div>--}}

                        {{--<div class="form-group">--}}
                            {{--<label for="password">{{ __('web.Password') }}</label>--}}


                                {{--<input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>--}}

                                {{--@if ($errors->has('password'))--}}
                                    {{--<span class="invalid-feedback">--}}
                                        {{--<strong>{{ $errors->first('password') }}</strong>--}}
                                    {{--</span>--}}
                                {{--@endif--}}

                        {{--</div>--}}


                            {{--<label>--}}
                                {{--<input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> {{ __('web.Remember Me') }}--}}
                                {{--<span></span>--}}
                            {{--</label>--}}


                        {{--<div class="form-group">--}}
                            {{--<div class="">--}}
                                {{--<button type="submit" class="btn btn-primary">--}}
                                    {{--{{ __('web.Login') }}--}}
                                {{--</button>--}}

                                {{--<a href="{{ url('auth/facebook') }}" class="btn btn-primary" title="Prihlásenie cez Facebook">--}}
                                    {{--FB vstup--}}
                                {{--</a>--}}
                            {{--</div>--}}
                        {{--</div>--}}


                        {{--<div style="border-top: solid 1px silver; margin-top: 1.1rem; padding-top: .6rem" class="d-flex-vertical">--}}
                            {{--<a href="{{ route('register') }}">Nová registrácia</a>--}}

                            {{--<a href="{{ route('password.request') }}">--}}
                            {{--{{ __('web.Forgot Your Password?') }}--}}
                            {{--</a>--}}
                        {{--</div>--}}
                    {{--</form>--}}
                {{--</div>--}}
            {{--</div>--}}
    {{--</div>--}}
</div>
@endsection
