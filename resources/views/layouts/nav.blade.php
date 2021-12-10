<nav class="bg-blue-900 text-gray-200 px-2 ">
    <div style="max-width: 80rem" class="flex mx-auto justify-between py-2 flex-wrap">

        <div class="flex">
            <img class="embeddedObject"
                src="https://content.screencast.com/users/fg-a/folders/christmas/media/7d014586-ce64-442b-a1e6-276c8414d7dc/ctree_5a.gif"
                width="25" height="25" border="0" alt="Clipart" />
            <a class="my-2 font-semibold ml-2" href="{{ url('/') }}">
                Hlas Cirkvi
            </a>
        </div>

        <ul class="my-2 flex  space-x-4">
            <li>
                <a class="flex items-center" href="{{ route('online-prenosy') }}">Nedeľné prenosy

                    @if (session()->has('countUnwatchedVideos'))
                        <div
                            class="w-5 h-5 p-3 bg-red-500 text-white rounded-full flex justify-center items-center ml-1">
                            <span class="pb-1">{{ session()->get('countUnwatchedVideos') }}</span>
                        </div>
                    @endif
                </a>
            </li>
            <li><a href="{{ route('konferencie.pute') }}">Vzdelávanie</a></li>
            {{-- <li><a href="{{ route('zdravie') }}">Zdravie z Božej ruky</a></li> --}}
        </ul>

        <ul class="my-2 flex space-x-4">
            <radio-button></radio-button>
            <li class="border-2 rounded-md px-2 whitespace-nowrap">
                <a href="{{ route('modlitby.index') }}" class="nav-link radio">
                    <i class="fas fa-praying-hands mr-2 text-gray-300"></i>
                    Modlitby
                </a>
            </li>

            <li class="border-2 rounded-md px-2 whitespace-nowrap">
                <a href="{{ route('akcie.index') }}" class="nav-link radio">
                    <i class="fa fa-share-alt mr-2 text-gray-300" aria-hidden="true"></i>
                    Podujatia
                </a>
            </li>

            {{-- <li><a href="{{ route('akcie.index') }}">Podujatia</a></li> --}}
        </ul>

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
