@extends('layouts.admin')

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Upraviť užívateľa
        </x-slot>

        <x-slot name="title_right">

        </x-slot>


        <x-slot name="page">
            <form method="post" action="{{ route('admin.user.update', [$user->id]) }}">
                @csrf @method('PUT')
                <div class="ar-panel ar-dash__new p-4 sm:p-5 w-full max-w-xl">

                    <div class="form-group">
                        <label for="first_name">Meno</label>
                        <input type="text" name="first_name" id="first_name" value="{{ $user->first_name }}"
                            class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Priezvisko</label>
                        <input type="text" name="last_name" id="last_name" value="{{ $user->last_name }}"
                            class="form-control" required>
                    </div>

                    @can('superadmin')
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" name="email" id="email" value="{{ $user->email }}" class="form-control"
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
