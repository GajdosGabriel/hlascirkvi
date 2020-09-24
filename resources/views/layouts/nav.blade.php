<nav>
    <div class="nav-content">
        <ul>
            <li class="navbar-nav">
                <a class="navbar-brand" href="{{ url('/') }}">
                    Hlas Cirkvi
                    {{--{{ config('app.name', 'Laravel') }}--}}
{{--                    <img src="{{ url('images/logo1.gif') }}" style="height: 4rem" alt="Logo traja králi">--}}
                </a>
            </li>
        </ul>

        <ul class="center">
            <li>
                <a href="{{ route('online-prenosy') }}">Nedeľné prenosy</a>
                @if( session()->has('countUnwatchedVideos') )
                    <span style="color: white; background: red; padding: 0.2rem 0.5rem;margin-left: -.2rem; font-size: 80%; border-radius: 2rem">
                        {{ session()->get('countUnwatchedVideos') }}</span>
                @endif
            </li>
            <li><a href="{{ route('konferencie.pute') }}">Vzdelávanie</a></li>
            {{--<li><a href="{{ route('zdravie') }}">Zdravie z Božej ruky</a></li>--}}
        </ul>
        <ul class="center">
{{--            <li><a href="{{ route('user.index') }}">Osobnosti</a></li>--}}
            <article-admin inline-template>
                <div>
                    <li @click="toggle()" class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link radio" href="#">
                            <i class="fa fa-volume-up"></i> Rádia
                            <i class="fas fa-caret-down"></i>
                        </a>
                    </li>

                    <ul v-cloak v-if="all" @click="toggle()" class="dropdown-menu">
                        <li><a title="Rádio 7 Slovenská redakcia" class="dropdown-item" href="#" onClick="window.open('http://radio7.sk/live-vysielanie','pagename','resizable,height=500,width=470')">Rádio 7 sk
                            <img style="height: 16px;margin-bottom: -3px;" src="{{ asset('images/flag-sk.jpg') }}">
                            </a></li>
                        <li><a title="Rádio 7 Česká redakcia" class="dropdown-item" href="#" onClick="window.open('http://listen.play.cz/player.html?shortcut=radio7&format=&v=20200120','pagename','resizable,height=500,width=470')">Rádio 7 cz
                                <img style="height: 16px;margin-bottom: -3px;" src="{{ asset('images/flag-cz.jpg') }}">
                            </a></li>
                        <li><a class="dropdown-item" href="#" onClick="window.open('https://www.lumen.sk/radio-streaming.html?liveplayer=1','pagename','resizable,height=500,width=470')">Rádio Lumen</a></li>
                        <li><a class="dropdown-item" href="#" onClick="window.open('https://www.lumen.sk/radio-streaming.html?liveplayer=2','pagename','resizable,height=500,width=470')">Lumen Gospel</a></li>
                        <li><a class="dropdown-item" href="#" onClick="window.open('https://dreamsiteradioplayer.it/plrm/rmslovakia/','pagename','resizable,height=500,width=470')">Rádio Mária</a></li>
                    </ul>
                </div>
            </article-admin>
            <li><a href="{{ route('event.index') }}">Podujatia</a></li>
        </ul>

        <ul>
        @guest
        <li><a href="{{ route('login') }}">{{ __('auth.login') }}</a></li>
        {{--<li><a href="{{ route('register') }}">{{ __('auth.Register') }}</a></li>--}}
        @else

            <bell></bell>

        <ul class="nav-item dropdown">

        <article-admin inline-template>
            <div>
                <li @click="toggle" class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link radio" href="#">
                                <span href="{{ route('organization.profile', [auth()->user()->org_id, auth()->user()->slug]) }}"
                                        {{--id="navbarDropdown" --}}
                                      class="nav-link" href="#">
                                    {{ auth()->user()->fullname }}
                                    {{--<i class="fas fa-caret-down"></i>--}}
                                </span>
                        <i class="fas fa-caret-down"></i>
                    </a>
                </li>

                <ul v-cloak v-if="all" @click="toggle" class="dropdown-menu">
                @can('admin')
                <li><a class="dropdown-item" href="{{ route('admin.home') }}">Admin</a></li>
                @endcan
                <li><a class="dropdown-item" href="{{ route('organization.profile', [auth()->user()->org_id, auth()->user()->slug]) }}">Profil</a></li>
                <li title="divider"></li>
                <li><a class="dropdown-item" href="{{ route('logout') }}"
                         onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
                        {{ __('web.Logout') }}
                    </a>
                </li>

            </div>
        </article-admin>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
                @endguest
            </ul>

        </ul>
    </div>
</nav>
