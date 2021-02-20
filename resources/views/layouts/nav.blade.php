<nav class="bg-blue-900 text-gray-200 px-2 ">
    <div style="max-width: 80rem" class="flex mx-auto justify-between py-2 flex-wrap">
        <ul class="my-2">
            <li>
                <a href="{{ url('/') }}">
                    Hlas Cirkvi
                </a>
            </li>
        </ul>

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
            {{--            <li><a href="{{ route('user.index') }}">Osobnosti</a></li>--}}
            <article-admin inline-template>
                <div>
                    <li @click="toggle()" class="border-2 rounded-md px-2">
                        <a id="navbarDropdown" class="" href="#">
                            <i class="fa fa-volume-up"></i> Rádia
                            <i class="fas fa-caret-down"></i>
                        </a>
                    </li>

                    <ul v-cloak v-if="all" @click="toggle()"
                        class="absolute z-10 flex flex-col bg-white rounded-md border-2 border-gray-500 text-gray-700">
                        <li class="p-2 px-6 hover:bg-gray-300">
                            <a title="Rádio 7 Slovenská redakcia" class="hover:text-gray-700 no-underline" href="#"
                               onClick="window.open('http://radio7.sk/live-vysielanie','pagename','resizable,height=500,width=470')">
                                <div class="flex">
                                    Rádio 7 sk
                                    <img class="h-5 ml-2" src="{{ asset('images/flag-sk.jpg') }}">
                                </div>
                            </a>
                        </li>
                        <li class="p-2 px-6 hover:bg-gray-300">
                            <a title="Rádio 7 Česká redakcia" class="hover:text-gray-700 no-underline" href="#"
                               onClick="window.open('http://listen.play.cz/player.html?shortcut=radio7&format=&v=20200120','pagename','resizable,height=500,width=470')">
                                <div class="flex">
                                    Rádio 7 cz
                                    <img class="h-5 ml-2" src="{{ asset('images/flag-cz.jpg') }}">
                                </div>
                            </a></li>
                        <li class="p-2 px-6 hover:bg-gray-300">
                            <a class="hover:text-gray-700 no-underline" href="#"
                               onClick="window.open('https://www.lumen.sk/radio-streaming.html?liveplayer=1','pagename','resizable,height=500,width=470')">Rádio
                                Lumen</a>
                        </li>
                        <li class="p-2 px-6 hover:bg-gray-300">
                            <a class="hover:text-gray-700 no-underline" href="#"
                               onClick="window.open('https://www.lumen.sk/radio-streaming.html?liveplayer=2','pagename','resizable,height=500,width=470')">Lumen
                                Gospel</a>
                        </li>
                        <li class="p-2 px-6 hover:bg-gray-300">
                            <a class="hover:text-gray-700 no-underline" href="#"
                               onClick="window.open('https://dreamsiteradioplayer.it/plrm/rmslovakia/','pagename','resizable,height=500,width=470')">Rádio
                                Mária</a>
                        </li>
                    </ul>
                </div>
            </article-admin>
            <li><a href="{{ route('event.index') }}">Podujatia</a></li>
            <li><a href="{{ route('modlitby.index') }}" class="nav-link radio">
                    <i style="color: #dcdcdc" class="fas fa-praying-hands mr-2"></i>Modlitby</a>
            </li>
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
