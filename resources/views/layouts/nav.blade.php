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

                    <x-navigation.li class="flex" route="{{ route('online-prenosy') }}">
                        Nedeľné prenosy
                        @if (session()->has('countUnwatchedVideos'))
                            <div
                                class="w-5 h-5 p-3 bg-red-500 text-white rounded-full flex justify-center items-center ml-1">
                                <span class="pb-1">{{ session()->get('countUnwatchedVideos') }}</span>
                            </div>
                        @endif
                    </x-navigation.li>

                    <x-navigation.li route="{{ route('konferencie.pute') }}">
                        Vzdelávanie
                    </x-navigation.li>

                </ul>

                <ul class="my-2 flex space-x-4">
                    <radio-button></radio-button>

                    <x-navigation.li route="{{ route('modlitby.index') }}">
                        <i class="fas fa-praying-hands mr-2 text-gray-300"></i>
                        Modlitby
                    </x-navigation.li>

                    <x-navigation.li route="{{ route('akcie.index') }}" class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                          </svg>

                        Podujatia
                    </x-navigation.li>

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
