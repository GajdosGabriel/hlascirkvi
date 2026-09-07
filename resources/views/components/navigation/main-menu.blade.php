@php
    $navLinks = [
        [
            'route' => route('online-prenosy'),
            'label' => 'Nedeľné prenosy',
            'icon' => 'video',
            'badge' => session()->get('countUnwatchedVideos'),
        ],
        [
            'route' => route('konferencie.pute'),
            'label' => 'Vzdelávanie',
            'icon' => 'book',
        ],
        [
            'route' => route('modlitby.index'),
            'label' => 'Modlitby',
            'icon' => 'pray',
        ],
        [
            'route' => route('akcie.index'),
            'label' => 'Podujatia',
            'icon' => 'calendar',
        ],
    ];
@endphp

<nav class="relative z-40 bg-blue-900 text-blue-100 shadow-lg">
    <div class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-2 px-2">

        {{-- Logo --}}
        <a class="flex shrink-0 items-center rounded-md px-2 py-1 text-lg font-semibold tracking-wide text-white transition-colors hover:bg-blue-800"
            href="{{ url('/') }}">
            Hlas Cirkvi
        </a>

        {{-- Menu pre veľké obrazovky --}}
        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($navLinks as $link)
                <x-navigation.main-menu-item :route="$link['route']" :badge="$link['badge'] ?? null">
                    <x-navigation.nav-icon :name="$link['icon']" />
                    {{ $link['label'] }}
                </x-navigation.main-menu-item>

                {{-- Rádiá si držia pôvodné miesto v poradí --}}
                @if ($loop->index === 1)
                    <radio-button></radio-button>
                @endif
            @endforeach
        </div>

        {{-- Prihlásenie / používateľ + hamburger --}}
        <div class="flex shrink-0 items-center gap-1">
            @guest
                <a class="rounded-md border border-blue-400 px-3 py-1.5 text-sm font-medium text-blue-50 transition-colors hover:bg-blue-800 hover:text-white"
                    href="{{ route('login') }}">{{ __('auth.login') }}</a>
            @else
                <navigation-main></navigation-main>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            @endguest

            <mobile-menu v-cloak>
                @foreach ($navLinks as $link)
                    <x-navigation.main-menu-item variant="mobile" :route="$link['route']" :badge="$link['badge'] ?? null">
                        <x-navigation.nav-icon :name="$link['icon']" size="h-5 w-5" />
                        {{ $link['label'] }}
                    </x-navigation.main-menu-item>

                    @if ($loop->index === 1)
                        <radio-button variant="mobile"></radio-button>
                    @endif
                @endforeach
            </mobile-menu>
        </div>

    </div>
</nav>
