@extends('layouts.admin')

@section('title')
    <title>{{ 'Používateľ · ' . $user->adminName() }}</title>
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">Detail používateľa</x-slot>
        <x-slot name="title_right">
            <a href="{{ route('admin.user.index') }}" class="ar-tab">Používatelia</a>
            <a href="{{ route('admin.logs.index', ['user' => $user->id]) }}" class="ar-tab">Denník</a>
            @unless ($user->trashed())
                <a href="{{ route('admin.user.edit', $user->id) }}" class="ar-tab"><i class="ph ph-pencil-simple mr-1" aria-hidden="true"></i>Upraviť</a>
            @endunless
        </x-slot>
        <x-slot name="page">
            @php
                $date = fn ($value) => $value?->format('d.m.Y H:i:s') ?? '—';
                $yes = fn ($value) => $value ? 'Áno' : 'Nie';
                $sections = [
                    'Profil' => [
                        'Meno' => $user->first_name,
                        'Priezvisko' => $user->last_name,
                        'E-mail' => $user->email,
                        'ID' => $user->id,
                        'UUID' => $user->uuid,
                        'Slug' => $user->slug,
                        'Oslovenie' => $user->vocative,
                        'Pohlavie podľa mena' => $user->gender?->label(),
                        'Popis' => $user->description,
                    ],
                    'Účet a overenie' => [
                        'Stav účtu' => $user->status->label(),
                        'Dôvod stavu' => $user->status_reason,
                        'Zmena stavu' => $date($user->status_changed_at),
                        'Stav zmenil' => $statusChangedBy?->adminName() ?? ($user->status_changed_by ? '#' . $user->status_changed_by : null),
                        'Blokácia (starý príznak)' => $yes($user->disabled),
                        'Overenie e-mailu' => $date($user->email_verified_at),
                        'Overený profil (starý príznak)' => $yes($user->verified),
                        'Registrácia' => $date($user->created_at),
                        'Posledná aktualizácia' => $date($user->updated_at),
                        'Zrušenie účtu' => $date($user->deleted_at),
                        'Roly' => $user->roles->pluck('name')->join(', '),
                        'Oprávnenia' => $permissions->join(', '),
                    ],
                    'Prihlásenie' => [
                        'Posledné prihlásenie' => $user->last_login_at ? $date($user->last_login_at) : 'Bez záznamu',
                        'Spôsob prihlásenia' => $user->last_login_via_label,
                        'Posledná IP adresa' => $user->last_login_ip,
                        'Aktívny kanál' => $user->canal?->title ?? ($user->canal_id ? '#' . $user->canal_id : null),
                    ],
                    'Nastavenia a e-maily' => [
                        'Odber e-mailov' => $yes($user->send_email),
                        'Odhlásenie z odberu' => $date($user->newsletter_unsubscribed_at),
                        'IP pri odhlásení z odberu' => $user->newsletter_unsubscribed_ip,
                        'Autor na úvodnej stránke' => $yes($user->front_author),
                        'Preferované cirkevné zaradenie (uložená hodnota)' => $user->set_denomination,
                        'Upozornenia prečítané do' => $date($user->notify_bell),
                    ],
                ];
            @endphp
            <x-dashboard.panel class="mb-5">
                <div class="flex flex-wrap items-center gap-4">
                    @if ($user->avatar)
                        <img src="{{ $user->avatarUrl() }}" alt="Profilová fotografia" class="h-16 w-16 rounded-full object-cover" loading="lazy">
                    @endif
                    <div class="min-w-0">
                        <h2 class="text-xl font-semibold break-words">{{ $user->adminName() }}</h2>
                        <p class="text-sm text-gray-500 break-all">{{ $user->email }}</p>
                    </div>
                    @if ($user->trashed())
                        <x-dashboard.status-badge label="Zrušený účet" tone="gray" />
                    @else
                        <x-dashboard.status-badge :badge="$user->accountBadge()" />
                    @endif
                </div>
            </x-dashboard.panel>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
                @foreach ($sections as $heading => $fields)
                    <x-dashboard.panel :title="$heading">
                        <dl class="space-y-3 text-sm">
                            @foreach ($fields as $label => $value)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 sm:gap-3">
                                    <dt class="text-gray-500">{{ $label }}</dt>
                                    <dd class="break-words whitespace-pre-wrap">{{ $value === null || $value === '' ? '—' : $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </x-dashboard.panel>
                @endforeach
            </div>

            <x-dashboard.panel title="Aktivita používateľa" class="mb-5">
                <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    @foreach (['Spravované kanály (vrátane zrušených)' => $user->canals->count(), 'Komentáre bez zrušených' => $user->commentss_count, 'Uložené príspevky bez zrušených' => $user->saved_posts_count] + $activity as $label => $count)
                        <div><dt class="text-gray-500">{{ $label }}</dt><dd class="text-xl font-semibold">{{ number_format($count, 0, ',', ' ') }}</dd></div>
                    @endforeach
                </dl>
            </x-dashboard.panel>

            <x-dashboard.panel title="Spravované kanály" class="mb-5" flush>
                <x-slot name="note">Príspevky a modlitby patria kanálu; nemusia byť vytvorené týmto používateľom.</x-slot>
                @forelse ($user->canals as $canal)
                    <article class="ar-item">
                        <div class="ar-item__body">
                            @if ($canal->trashed())
                                <span class="font-semibold">{{ $canal->title }}</span>
                            @else
                                <a href="{{ route('admin.canal.show', $canal->id) }}" class="font-semibold hover:underline">{{ $canal->title }}</a>
                            @endif
                            <div class="ar-item__meta">
                                <span>#{{ $canal->id }}</span>
                                <span>{{ $canal->identity_mode?->label() }}</span>
                                <span>{{ $canal->posts_count }} príspevkov · {{ $canal->prayers_count }} modlitieb</span>
                                @if ($canal->id == $user->canal_id)<span>Aktívny kanál</span>@endif
                                @if ($canal->trashed())<span>Zrušený</span>@elseif (! $canal->published)<span>Nezverejnený</span>@endif
                            </div>
                        </div>
                    </article>
                @empty
                    <x-dashboard.empty>Používateľ nespravuje žiadny kanál.</x-dashboard.empty>
                @endforelse
            </x-dashboard.panel>

            <x-dashboard.panel title="Posledné komentáre" class="mb-5" flush>
                <x-slot name="note">{{ $comments->total() }} spolu vrátane zrušených</x-slot>
                @forelse ($comments as $comment)
                    <article class="ar-item">
                        <div class="ar-item__body">
                            <div class="ar-item__meta">
                                <span>#{{ $comment->id }} · {{ $date($comment->created_at) }}</span>
                                <span>{{ $comment->trashed() ? 'Zrušený' : ($comment->published ? 'Zverejnený' : 'Nezverejnený') }}</span>
                            </div>
                            <p class="text-sm whitespace-pre-wrap break-words mt-2">{{ $comment->body }}</p>
                        </div>
                    </article>
                @empty
                    <x-dashboard.empty>Žiadne komentáre.</x-dashboard.empty>
                @endforelse
                <x-slot name="footer">{{ $comments->links() }}</x-slot>
            </x-dashboard.panel>

            <x-dashboard.panel title="Posledné udalosti a e-maily" class="mb-5" flush>
                <x-slot name="note">Denník uchováva info {{ config('logging.system_log.days', 31) }} dní, varovania a chyby {{ config('logging.system_log.error_days', 90) }} dní.</x-slot>
                @forelse ($logs as $log)
                    <article class="ar-item">
                        <div class="ar-item__body">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-dashboard.status-badge :label="$log->event" :tone="['error' => 'red', 'warning' => 'amber'][$log->level] ?? 'gray'" />
                                <span class="text-xs text-gray-500">{{ $date($log->created_at) }} · {{ $log->status ?? '—' }}</span>
                            </div>
                            <p class="text-sm break-words mt-2">{{ $log->message }}</p>
                            @if ($log->ip)<p class="text-xs text-gray-500 mt-1">{{ $log->ip }}</p>@endif
                        </div>
                    </article>
                @empty
                    <x-dashboard.empty>Žiadne zachované udalosti.</x-dashboard.empty>
                @endforelse
                <x-slot name="footer">{{ $logs->links() }}</x-slot>
            </x-dashboard.panel>

            <x-dashboard.panel title="Aktívne relácie">
                <x-slot name="note">Najviac 20 posledných relácií v rámci doby platnosti.</x-slot>
                @if ($sessions === null)
                    <p class="text-sm text-gray-500">Pri aktuálnom spôsobe ukladania relácií nie je ich zoznam dostupný.</p>
                @else
                    @forelse ($sessions as $session)
                        <div class="text-sm py-3 border-b border-gray-200">
                            <p>{{ \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity, config('app.timezone'))->format('d.m.Y H:i:s') }} · {{ $session->ip_address ?? '—' }}</p>
                            <p class="text-gray-500 break-all mt-1">{{ $session->user_agent ?? 'Neznámy prehliadač' }}</p>
                        </div>
                    @empty
                        <x-dashboard.empty>Žiadne aktívne relácie.</x-dashboard.empty>
                    @endforelse
                @endif
            </x-dashboard.panel>
        </x-slot>
    </x-pages.admin>
@endsection