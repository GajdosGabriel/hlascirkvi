@extends('layouts.admin')

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Upraviť užívateľa
        </x-slot>

        <x-slot name="title_right">
            <a href="{{ route('admin.logs.index', ['user' => $user->id]) }}" class="ar-tab" title="Maily, prihlásenia a ďalšie udalosti tohto používateľa">
                <i class="far fa-list-alt mr-1"></i>Denník
            </a>
        </x-slot>


        <x-slot name="page">
            <form method="post" action="{{ route('admin.user.update', [$user->id]) }}">
                @csrf @method('PUT')
                <div class="ar-panel ar-dash__new p-4 sm:p-5 w-full max-w-xl">

                    <div class="form-group">
                        <label for="first_name">Meno</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" maxlength="255"
                            class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Priezvisko</label>
                        {{-- Priezvisko nie je povinné (validácia v Admin\UserController
                             ho pripúšťa prázdne) a vyše 70 účtov ho nemá — required
                             tu bránil uložiť čokoľvek iné, napr. stav účtu. --}}
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}"
                            class="form-control" maxlength="255">
                    </div>

                    @can('superadmin')
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="status">{{ __('model_status.account_status') }}</label>
                            <select name="status" id="status" class="form-control" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" @selected(old('status', $user->status->value) === $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')<p class="text-red-700 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="status_reason">{{ __('model_status.status_reason') }}</label>
                            <textarea name="status_reason" id="status_reason" maxlength="500" rows="3"
                                class="form-control">{{ old('status_reason', $user->status_reason) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ __('model_status.status_reason_hint') }}</p>
                            @error('status_reason')<p class="text-red-700 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="rounded-md bg-gray-100 p-3 my-3 text-sm">
                            <div><strong>Posledné prihlásenie:</strong> {{ $user->last_login_at?->format('d.m.Y H:i:s') ?? 'Nikdy' }}</div>
                            <div><strong>Spôsob:</strong> {{ $user->last_login_via_label ?? '—' }}</div>
                            <div><strong>IP adresa:</strong> {{ $user->last_login_ip ?? '—' }}</div>
                        </div>
                    @endcan

                </div>

                <x-dashboard.form-bar class="max-w-xl" :cancel="route('admin.user.index')" />
            </form>
        </x-slot>
        </x-pages.admin>
    @endsection
