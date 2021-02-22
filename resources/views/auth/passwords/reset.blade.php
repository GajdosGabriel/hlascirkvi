@extends('layouts.app')

@section('content')
    <div class="page">
        <div class="mx-auto max-w-sm">
            <div class="py-10 text-center">

            </div>

            <div class="bg-white rounded shadow border-gray-300 border-2">
                <div class="border-b py-8 font-bold text-black text-center text-xl tracking-widest uppercase">
                    Resetovať heslo
                </div>


                <div class="card-body">
                    <form method="POST" action="{{ route('password.request') }}" class="bg-grey-lightest px-10 py-10">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-right">Emailová adresa</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="border w-full p-3 {{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-right">Nové heslo</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="border w-full p-3 {{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="nové heslo" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">Potvrdiť heslo</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="border w-full p-3" name="password_confirmation" placeholder="potvrdiť nové heslo" required>
                            </div>
                        </div>

                        <div class="flex mt-5">
                            <button type="submit"
                                    class="hover:bg-blue-300 border-2 border-gray-300 rounded-md w-full p-4 text-sm uppercase font-bold tracking-wider">
                                Uložiť nové heslo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
