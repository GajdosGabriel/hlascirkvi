@extends('layouts.admin')

@section('title')
    <title>{{ 'Upraviť používateľa · ' . $user->adminName() }}</title>
@endsection

@section('own-errors', '1')

@section('content')
    <x-pages.admin>
        <x-slot name="title">Upraviť používateľa</x-slot>
        <x-slot name="title_right">
            <a href="{{ route('admin.user.index') }}" class="ar-tab">Používatelia</a>
            <a href="{{ route('admin.user.show', $user->id) }}" class="ar-tab">Zobraziť</a>
            <a href="{{ route('admin.logs.index', ['user' => $user->id]) }}" class="ar-tab" title="E-maily, prihlásenia a ďalšie udalosti používateľa">
                <i class="ph ph-list-bullets mr-1" aria-hidden="true"></i>Denník
            </a>
        </x-slot>
        <x-slot name="page">
            <x-dashboard.panel class="mb-5">
                <div class="flex flex-wrap items-center gap-4">
                    @if ($user->avatar)
                        <img src="{{ $user->avatarUrl() }}" alt="Profilová fotografia" class="h-16 w-16 rounded-full object-cover">
                    @endif
                    <div class="min-w-0 flex-1">
                        <h2 class="text-xl font-semibold break-words">{{ $user->adminName() }}</h2>
                        <p class="text-sm text-gray-500 break-all">#{{ $user->id }} · {{ $user->email }}</p>
                    </div>
                    <x-dashboard.status-badge :badge="$user->accountBadge()" />
                </div>
            </x-dashboard.panel>

            @if ($errors->any())
                <div role="alert" class="rounded-md border border-red-200 bg-red-50 p-4 mb-5 text-sm text-red-700">
                    <p class="font-semibold">Zmeny sa neuložili. Skontrolujte vyznačené polia.</p>
                    <ul class="list-disc pl-5 mt-2">
                        @foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('admin.user.update', $user->id) }}">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
                    <div class="xl:col-span-2 space-y-5 min-w-0">
                        <x-dashboard.panel title="Profil používateľa">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                                <div class="form-group">
                                    <label for="first_name">Meno <span class="text-gray-500">(povinné)</span></label>
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}"
                                        maxlength="50" autocomplete="given-name" class="form-control" required
                                        aria-invalid="{{ $errors->has('first_name') ? 'true' : 'false' }}" @error('first_name') aria-describedby="first_name_error" @enderror>
                                    @error('first_name')<p id="first_name_error" class="text-red-700 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Priezvisko</label>
                                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}"
                                        maxlength="50" autocomplete="family-name" class="form-control"
                                        aria-invalid="{{ $errors->has('last_name') ? 'true' : 'false' }}" @error('last_name') aria-describedby="last_name_error" @enderror>
                                    @error('last_name')<p id="last_name_error" class="text-red-700 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail <span class="text-gray-500">(povinné)</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                    maxlength="100" autocomplete="email" class="form-control" required
                                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" aria-describedby="email_hint{{ $errors->has('email') ? ' email_error' : '' }}">
                                <p id="email_hint" class="text-xs text-gray-500 mt-1">Adresa slúži na prihlásenie a doručovanie e-mailov. Musí byť jedinečná.</p>
                                @error('email')<p id="email_error" class="text-red-700 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label for="description">Popis profilu</label>
                                <textarea name="description" id="description" maxlength="5000" rows="5" class="form-control"
                                    aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" aria-describedby="description_hint{{ $errors->has('description') ? ' description_error' : '' }}">{{ old('description', $user->description) }}</textarea>
                                <p id="description_hint" class="text-xs text-gray-500 mt-1">Voliteľný popis používateľa, najviac 5000 znakov.</p>
                                @error('description')<p id="description_error" class="text-red-700 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </x-dashboard.panel>

                        <x-dashboard.panel title="Prístup k účtu">
                            @if ($user->is(auth()->user()))
                                <p class="text-sm text-gray-500 mb-4">Upravujete vlastný administrátorský účet. Jeho prístup nemôžete zneprístupniť.</p>
                            @endif
                            <div class="form-group">
                                <label for="status">{{ __('model_status.account_status') }}</label>
                                <select name="status" id="status" class="form-control" required
                                    aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" aria-describedby="status_hint{{ $errors->has('status') ? ' status_error' : '' }}">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected(old('status', $user->status->value) === $status->value)
                                            @disabled($user->is(auth()->user()) && ! $status->isActive())>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <p id="status_hint" class="text-xs text-gray-500 mt-1">Iba aktívny účet má prístup. Pri inom stave sa zrušia aj jeho API prístupy.</p>
                                @error('status')<p id="status_error" class="text-red-700 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label for="status_reason">{{ __('model_status.status_reason') }}</label>
                                <textarea name="status_reason" id="status_reason" maxlength="500" rows="3" class="form-control"
                                    aria-invalid="{{ $errors->has('status_reason') ? 'true' : 'false' }}" aria-describedby="status_reason_hint{{ $errors->has('status_reason') ? ' status_reason_error' : '' }}">{{ old('status_reason', $user->status_reason) }}</textarea>
                                <p id="status_reason_hint" class="text-xs text-gray-500 mt-1">Pri neaktívnom účte je dôvod povinný. Najviac 500 znakov.</p>
                                @error('status_reason')<p id="status_reason_error" class="text-red-700 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </x-dashboard.panel>
                    </div>

                    <aside class="space-y-5 min-w-0" aria-label="Informácie o účte">
                        <x-dashboard.panel title="Účet a overenie">
                            <dl class="space-y-4 text-sm">
                                <div><dt class="text-gray-500">Registrácia</dt><dd>{{ $user->created_at?->format('d.m.Y H:i') ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Overenie e-mailu</dt><dd>{{ $user->email_verified_at?->format('d.m.Y H:i') ?? 'E-mail nie je overený' }}</dd></div>
                                <div><dt class="text-gray-500">Roly</dt><dd class="break-words">{{ $user->roles->pluck('name')->join(', ') ?: 'Bez roly' }}</dd></div>
                                <div><dt class="text-gray-500">Posledná úprava</dt><dd>{{ $user->updated_at?->format('d.m.Y H:i') ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Posledná zmena stavu</dt><dd>{{ $user->status_changed_at?->format('d.m.Y H:i') ?? 'Bez záznamu' }}</dd></div>
                            </dl>
                        </x-dashboard.panel>
                        <x-dashboard.panel title="Posledné prihlásenie">
                            <dl class="space-y-4 text-sm">
                                <div><dt class="text-gray-500">Dátum a čas</dt><dd>{{ $user->last_login_at?->format('d.m.Y H:i:s') ?? 'Bez záznamu' }}</dd></div>
                                <div><dt class="text-gray-500">Spôsob</dt><dd>{{ $user->last_login_via_label ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">IP adresa</dt><dd class="break-all">{{ $user->last_login_ip ?? '—' }}</dd></div>
                            </dl>
                        </x-dashboard.panel>
                    </aside>
                </div>
                <x-dashboard.form-bar :cancel="route('admin.user.show', $user->id)" />
            </form>
        </x-slot>
    </x-pages.admin>
@endsection
