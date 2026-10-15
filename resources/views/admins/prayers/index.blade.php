@extends('layouts.admin')
@php
    $seo = ['title' => 'Modlitby · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">Modlitby</x-slot>

        <x-slot name="title_right">
            {{-- Modlitba sa zakladá do kanála, takže bez prideleného kanála
                 nie je kam odkázať — route() by na prázdnom {canal} spadla. --}}
            @if (auth()->user()->canal_id)
                <a class="ar-btn ar-btn--accent" href="{{ route('profile.canals.prayers.create', auth()->user()->canal_id) }}">
                    <i class="ph ph-plus" aria-hidden="true"></i> Nová modlitba
                </a>
            @endif
        </x-slot>

        <x-slot name="page">
            {{-- Riadky ako vo výpise modlitieb kanála. Text sa skracuje — celé
                 prosby pod sebou robili z výpisu nekonečný stĺpec. --}}
            <x-dashboard.panel flush>
                @forelse ($prayers as $prayer)
                    <article class="ar-item">
                        <div class="ar-item__body">
                            <span class="ar-item__title">{{ $prayer->title ?: 'Bez nadpisu' }}</span>

                            <p class="mt-1 text-sm text-[color:var(--ar-ink-soft)]">
                                {{ \Illuminate\Support\Str::limit(strip_tags($prayer->body), 220) }}
                            </p>

                            <div class="ar-item__meta">
                                <span><i class="ph ph-user"></i>{{ $prayer->user_name ?: 'návštevník' }}</span>
                                @if ($prayer->canal)
                                    <a class="ar-link" href="{{ route('organizations.show', $prayer->canal_id) }}"><i class="ph ph-broadcast"></i>{{ $prayer->canal->title }}</a>
                                @endif
                                <time datetime="{{ $prayer->created_at->toIso8601String() }}" title="Vytvorené">
                                    {{ $prayer->created_at->format('j. n. Y') }}
                                </time>
                            </div>

                            <div class="ar-item__tags">
                                @if ($prayer->trashed())
                                    <span class="ar-badge ar-badge--count">Zrušená</span>
                                @elseif (! $prayer->published)
                                    <span class="ar-badge ar-badge--warn">Nezverejnená</span>
                                @endif
                                @if ($prayer->fulfilled_at)
                                    <span class="ar-badge ar-badge--ok">Vypočutá</span>
                                @endif
                                @if (! $prayer->trashed() && ! $prayer->canal)
                                    <span class="ar-badge ar-badge--count">Kanál už nie je dostupný</span>
                                @endif
                            </div>
                        </div>

                        @if (! $prayer->trashed() && $prayer->canal)
                            <div class="ar-item__actions">
                                <dropdown-slot label="Spravovať modlitbu">
                                    <a href="{{ route('profile.canals.prayers.edit', [$prayer->canal_id, $prayer->id]) }}">
                                        <i class="ph ph-pencil-simple" aria-hidden="true"></i> Upraviť
                                    </a>

                                    <hr class="ui-dropdown__divider">

                                    <form action="{{ route('profile.canals.prayers.destroy', [$prayer->canal_id, $prayer->id]) }}"
                                          method="post" data-confirm="Naozaj zmazať túto modlitbu?">
                                        @method('DELETE') @csrf
                                        <button type="submit" class="ui-dropdown__item--danger">
                                            <i class="ph ph-trash" aria-hidden="true"></i> Zmazať
                                        </button>
                                    </form>
                                </dropdown-slot>
                            </div>
                        @endif
                    </article>
                @empty
                    <x-dashboard.empty>
                        Žiadne modlitby nezodpovedajú tomuto výberu.
                        <a class="ar-link ml-1" href="{{ route('admin.prayer.index') }}">Zobraziť všetky</a>
                    </x-dashboard.empty>
                @endforelse
            </x-dashboard.panel>

            @if ($prayers->hasPages())
                <div class="mt-8">{{ $prayers->onEachSide(1)->links() }}</div>
            @endif
        </x-slot>
    </x-pages.admin>
@endsection
