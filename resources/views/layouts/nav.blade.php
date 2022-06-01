<nav class="bg-blue-900 text-gray-200 px-2 ">
    <div style="max-width: 80rem" class="flex mx-auto py-2 justify-between flex-wrap">

        <div class="flex">
            {{-- Vianočný stromček --}}
            {{-- <img class="embeddedObject"
                src="https://content.screencast.com/users/fg-a/folders/christmas/media/7d014586-ce64-442b-a1e6-276c8414d7dc/ctree_5a.gif"
                width="25" height="25" border="0" alt="Clipart" /> --}}
            <a class="my-2 font-semibold ml-2" href="{{ url('/') }}">
                Hlas Cirkvi
            </a>
        </div>

        <div class="hidden sm:block">
            <div class="flex">

                <ul class="my-2 flex  space-x-4 mr-4">
                    <li>
                        <x-navigation.link class="flex" href="{{ route('online-prenosy') }}"
                            active="{{ request()->routeIs('online-prenosy') }}">
                            Nedeľné prenosy

                            @if (session()->has('countUnwatchedVideos'))
                                <div
                                    class="w-5 h-5 p-3 bg-red-500 text-white rounded-full flex justify-center items-center ml-1">
                                    <span class="pb-1">{{ session()->get('countUnwatchedVideos') }}</span>
                                </div>
                            @endif
                        </x-navigation.link>
                    </li>
                    <li>
                        <x-navigation.link href="{{ route('konferencie.pute') }}"
                            active="{{ request()->routeIs('konferencie.pute') }}">Vzdelávanie</x-navigation.link>
                    </li>
                    {{-- <li><a href="{{ route('zdravie') }}">Zdravie z Božej ruky</a></li> --}}
                </ul>

                <ul class="my-2 flex space-x-4">
                    <radio-button></radio-button>
                    <li class="border-2 rounded-md px-2 whitespace-nowrap">
                        <x-navigation.link href="{{ route('modlitby.index') }}"
                            active="{{ request()->routeIs('modlitby.index') }}">
                            <i class="fas fa-praying-hands mr-2 text-gray-300"></i>
                            Modlitby
                        </x-navigation.link>
                    </li>

                    <li class="border-2 rounded-md px-2 whitespace-nowrap">
                        <x-navigation.link href="{{ route('akcie.index') }}"
                            active="{{ request()->routeIs('akcie.index') }}">
                            <i class="fa fa-share-alt mr-2 text-gray-300" aria-hidden="true"></i>
                            Podujatia
                        </x-navigation.link>
                    </li>

                    {{-- <li><a href="{{ route('akcie.index') }}">Podujatia</a></li> --}}
                </ul>
            </div>
        </div>
        <ul class="my-2 flex items-center">
            @guest
                <li><a href="{{ route('login') }}">{{ __('auth.login') }}</a></li>
                {{-- <li><a href="{{ route('register') }}">{{ __('auth.Register') }}</a></li> --}}
            @else
                <ul class="">

                    <navigation-main></navigation-main>

                </ul>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>

            @endguest

        </ul>


    </div>
</nav>
