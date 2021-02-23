<nav class="bg-blue-900 text-gray-200 px-2 ">
    <div style="max-width: 80rem" class="flex mx-auto justify-between py-2 flex-wrap">

        <a class="my-2" href="{{ url('/') }}">
            Hlas Cirkvi
        </a>

        <ul class="my-2 flex  space-x-4">
            <li>
                <a class="flex items-center" href="{{ route('online-prenosy') }}">Nedeľné prenosy

                    @if( session()->has('countUnwatchedVideos') )
                        <div class="w-7 h-7 bg-red-500 text-white rounded-full flex justify-center items-center ml-1">
                            {{ session()->get('countUnwatchedVideos') }}
                        </div>
                    @endif
                </a>
            </li>
            <li><a href="{{ route('konferencie.pute') }}">Vzdelávanie</a></li>
            {{--<li><a href="{{ route('zdravie') }}">Zdravie z Božej ruky</a></li>--}}
        </ul>

        <ul class="my-2 flex space-x-4">
            <radio-button></radio-button>
            <li class="border-2 rounded-md px-2"><a href="{{ route('modlitby.index') }}" class="nav-link radio">
                    <i class="fas fa-praying-hands mr-2 text-gray-300"></i>Modlitby</a>
            </li>
            <li><a href="{{ route('event.index') }}">Podujatia</a></li>
        </ul>

        <ul class="my-2">
            @guest
                <li><a href="{{ route('login') }}">{{ __('auth.login') }}</a></li>
                {{--<li><a href="{{ route('register') }}">{{ __('auth.Register') }}</a></li>--}}
            @else

                <bell></bell>

                <ul class="">

                    <article-admin inline-template>
                        <div class="relative z-10">
                            <li @click="toggle" class="">
                                <a id="navbarDropdown" class="nav-link radio" href="#">
                                <span
                                    href="{{ route('organization.profile', [auth()->user()->org_id, auth()->user()->slug]) }}"
                                    {{--id="navbarDropdown" --}}
                                    class="nav-link" href="#">
                                    {{ auth()->user()->fullname }}
                                    {{--<i class="fas fa-caret-down"></i>--}}
                                </span>
                                    <i class="fas fa-caret-down"></i>
                                </a>
                            </li>

                            <ul v-cloak v-if="all" @click="toggle"
                                class="flex flex-col absolute right-0 border-2 border-gray-500 bg-white text-gray-500 rounded-md">
                                @can('admin')
                                    <li class="p-2 px-6 hover:bg-gray-300 hover:text-gray-700">
                                        <a class="hover:text-gray-700"
                                           href="{{ route('admin.home') }}">Admin</a>
                                    </li>
                                @endcan
                                <li class="p-2 px-6 hover:bg-gray-300">
                                    <a class="hover:text-gray-700"
                                       href="{{ route('organization.profile', [auth()->user()->org_id, auth()->user()->slug]) }}">Profil</a>
                                </li>
                                {{--                                <li title="divider"></li>--}}
                                <li class="p-2 px-6 hover:bg-gray-300 hover:text-gray-700">
                                    <a class="hover:text-gray-700" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                        {{ __('web.Logout') }}
                                    </a>
                                </li>

                        </div>
                    </article-admin>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    @endguest
                </ul>


        </ul>
    </div>
</nav>
