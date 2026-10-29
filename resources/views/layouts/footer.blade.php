{{-- Rovnaká modrá rodina ako hlavná navigácia (bg-blue-900), o tón tmavšia. --}}
<footer class="bg-blue-950 p-5 text-blue-100">


    <div class="max-w-7xl mx-auto md:flex justify-around">

        <div class="w-full">
            <h2 class="border-b-2 border-blue-800 py-3 font-semibold text-white">O projekte</h2>
            <ul class="mt-2 space-y-1">
                <li><a href="{{ route('register') }}">Založiť svoj blog</a></li>
                {{-- Podujatia sa zakladajú na portáli tixio.eu, tu už nie. --}}
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

        {{-- Kotva #napiste-nam: odkazujú sem staršie e-maily. Správa ide do
             podpory (SupportController) — ostane zaevidovaná a odpoveď príde
             do nástenky aj e-mailom. --}}
        <div class="w-full" id="napiste-nam">
            <h2 class="border-b-2 border-blue-800 py-3 font-semibold text-white">Napíšte nám</h2>
            {{-- Neprihláseným sa formulár neukazuje, chodil cez neho spam. --}}
            @guest
                <p class="my-4">
                    Správu nám môžete poslať po <a href="{{ route('login') }}" class="underline">prihlásení</a>.
                </p>
            @else
            <form method="post" action="{{ route('profile.support.store') }}" class="my-4 space-y-2">
                @csrf
                <input type="hidden" name="page_url" value="{{ url()->full() }}">
                <label class="sr-only" for="footer-support-category">Téma</label>
                <select id="footer-support-category" name="category" required
                        class="w-full rounded-md border-2 border-gray-500 bg-white p-2 text-gray-900">
                    @foreach (\App\Enums\SupportCategory::options() as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                {{-- <textarea> atribút value ignoruje — text po chybe validácie mizol. --}}
                <label class="sr-only" for="footer-support-body">Správa</label>
                <textarea id="footer-support-body" class="border-2 border-gray-500 rounded-md w-full p-2 text-gray-900" name="body" rows="3"
                          minlength="3" maxlength="5000" required placeholder="Napíšte nám svoje podnety ... Ďakujeme">{{ old('body') }}</textarea>
                @error('body')<p class="text-sm text-red-300">{{ $message }}</p>@enderror

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <button type="submit"
                            class="px-2 p-1 border-2 text-sm rounded-sm hover:bg-blue-900">Odoslať
                        <span class="ph ph-paper-plane-tilt"></span>
                    </button>
                    <a href="{{ route('profile.support.index') }}" class="text-sm underline">Moje správy podpore</a>
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
