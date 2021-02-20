@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 min-h-screen">
        <div class="mx-auto max-w-sm">
            <div class="py-10 text-center">

            </div>

            <div class="bg-white rounded shadow border-gray-300 border-2">
                <div class="border-b py-8 font-bold text-black text-center text-xl tracking-widest uppercase">
                    {{ __('web.Reset Password') }}
                </div>


                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="bg-grey-lightest px-10 py-10">
                        @csrf

                        <div class="mb-3">
                            <label for="email"
                                   class="">{{ __('web.E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" placeholder="vaša emailová adresa"
                                       class="border w-full p-3 {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                       name="email"
                                       value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex mt-5">
                            <button type="submit"
                                    class="hover:bg-blue-300 border-2 border-gray-300 rounded-md w-full p-4 text-sm uppercase font-bold tracking-wider">
                                {{ __('web.Send Password Reset Link') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
