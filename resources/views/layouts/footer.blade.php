{{-- Rovnaká modrá rodina ako hlavná navigácia (bg-blue-900), o tón tmavšia. --}}
<footer class="bg-blue-950 p-5 text-blue-100">


    <div class="max-w-7xl mx-auto md:flex justify-around">

        <div class="w-full">
            <h2 class="border-b-2 border-blue-800 py-3 font-semibold text-white">O projekte</h2>
            <ul class="mt-2 space-y-1">
                <li><a href="{{ route('register') }}">Založiť svoj blog</a></li>
                {{-- Podujatia sa zakladajú na portáli event.hlascirkvi.sk, tu už nie. --}}
                <li><a href="{{ config('eventportal.url') }}/nahrat-plagat" target="_blank" rel="noopener nofollow">Pridať podujatie</a></li>
                <li><a href="{{ route('gdpr') }}">Ochrana osobných údajov</a></li>
            </ul>
        </div>

        <div class="w-full">
            <h2 class="border-b-2 border-blue-800 py-3 font-semibold text-white">Kontakt</h2>
            <ul class="mt-2 space-y-1">
                <li><span class="ph ph-globe"></span> Trenčín, Slovensko</li>
                <li><span class="ph ph-phone"></span> 0905 320 616</li>
                {{--<p><span class="ph ph-envelope-simple"></span><a href="#" data-toggle="modal" data-target="#contact" data-whatever="@mdo" >Napíšte nám</a></p>--}}
            </ul>

        </div>

        {{-- Kotva: jediný formulár na správu pre administrátora webu je tento,
             odkazuje sem aj nástenka užívateľa bez kanála. --}}
        <div class="w-full" id="napiste-nam">
            <h2 class="border-b-2 border-blue-800 py-3 font-semibold text-white">Napíšte nám</h2>
            {{-- Neprihláseným sa formulár neukazuje, chodil cez neho spam. --}}
            @guest
                <p class="my-4">
                    Správu nám môžete poslať po <a href="{{ route('login') }}" class="underline">prihlásení</a>.
                </p>
            @else
            <form method="post" action="{{ route('messengers.store') }}" class="my-4">
                {{ csrf_field() }}
                {{-- Bývalé „Som človek 7 plus 3" nahradila neviditeľná kontrola,
                     vysvetlenie v App\Support\HumanCheck. --}}
                <x-human-check/>
                {{-- <textarea> atribút value ignoruje — text po chybe validácie mizol. --}}
                <textarea class="border-2 border-gray-500 rounded-md w-full p-2" name="body" rows="3"
                          minlength="3" required placeholder="Napíšte nám svoje podnety ... Ďakujeme">{{ old('body') }}</textarea>

                <div class="form-group md:flex items-center mt-4">

                    <button type="submit"
                            class="px-2 p-1 border-2 text-sm rounded-sm mt-2 hover:bg-blue-900">Odoslať
                        <span class="glyphicon glyphicon-envelope"></span>
                    </button>
                </div>

            </form>
            @endguest


            {{--<p>--}}
            {{--<a class="ph ph-twitter-logo footer-socialicon" target="_blank" href="https://twitter.com/"></a>--}}
            {{--<a class="ph ph-facebook-logo footer-socialicon" target="_blank" href="https://www.facebook.com/"></a>--}}
            {{--<a class="ph ph-google-logo footer-socialicon" target="_blank" href="https://plus.google.com/"></a>--}}
            {{--<a class="ph ph-linkedin-logo footer-socialicon" target="_blank" href="https://plus.google.com/"></a>--}}
            {{--</p>--}}

        </div>
    </div>

    <div class="flex w-full justify-center">
        <div class="text-sm my-2">Autor šablóny Gajdoš Gabriel 2018
            <a href="{{ url('/') }}" title="Hlas Cirkvi.sk">Hlas Cirkvi.sk</a>
        </div>
    </div>

</footer>
