<footer style="background: #1e2e37; color: #a6a6a6" class="p-5">


    <div class="max-w-7xl mx-auto md:flex justify-around">

        <div class="w-full">
            <h4 class="border-b-2 py-3 border-gray-300 font-semibold">O projekte</h4>
            <ul class="mt-2 space-y-1">
                <li><a href="{{ route('register') }}">Založiť svoj blog</a></li>
                <li><a href="{{ route('akcie.index') }}">Pridať novú akciu</a></li>
                <li><a href="{{ route('gdpr') }}">Ochrana osobných údajov</a></li>
            </ul>
        </div>

        <div class="w-full">
            <h4 class="border-b-2 py-3 border-gray-300 font-semibold">Kontakt</h4>
            <ul class="mt-2 space-y-1">
                <li><span class="fa fa-globe"></span> Trenčín, Slovensko</li>
                <li><span class="fa fa-phone"></span> 0905 320 616</li>
                {{--<p><span class="fa fa-envelope"></span><a href="#" data-toggle="modal" data-target="#contact" data-whatever="@mdo" >Napíšte nám</a></p>--}}
            </ul>

        </div>

        <div class="w-full">
            <h4 class="border-b-2 py-3 border-gray-300 font-semibold">Napíšte nám</h4>
            <form method="post" action="{{ route('messengers.store') }}" class="my-4">
                {{ csrf_field() }}
                <textarea class="border-2 border-gray-500 rounded-md w-full p-2" name="body" rows="3"
                          class="w-full rounded-md" required placeholder="Napíšte nám svoje podnety ... Ďakujeme"
                          value="{{ old('body') }}"></textarea>

                <div class="form-group md:flex items-center mt-4">

                    @if(auth()->guest())
                        <label>Som človek 5+3 = </label>
                        <input class="mx-2 rounded-sm text-gray-800" type="number" name="iamHuman"
                               placeholder="Zadajte číslo 8"
                               required>
                    @endif

                    <button type="submit"
                            class="px-2 p-1 border-2 text-sm rounded-sm mt-2 hover:bg-gray-700">Odoslať
                        <span class="glyphicon glyphicon-envelope"></span>
                    </button>
                </div>

            </form>


            {{--<p>--}}
            {{--<a class="fa fa-twitter footer-socialicon" target="_blank" href="https://twitter.com/"></a>--}}
            {{--<a class="fa fa-facebook footer-socialicon" target="_blank" href="https://www.facebook.com/"></a>--}}
            {{--<a class="fa fa-google-plus footer-socialicon" target="_blank" href="https://plus.google.com/"></a>--}}
            {{--<a class="fa fa-linkedin footer-socialicon" target="_blank" href="https://plus.google.com/"></a>--}}
            {{--</p>--}}

        </div>
    </div>

    <div class="flex w-full justify-center">
        <div class="text-sm my-2">Autor šablóny Gajdoš Gabriel 2018
            <a href="{{ url('/') }}" title="Hlas Cirkvi.sk">Hlas Cirkvi.sk</a>
        </div>
    </div>

</footer>
