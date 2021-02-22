<footer style="background: #1e2e37; color: #a6a6a6" class="p-5">



    <div  class="max-w-7xl mx-auto flex justify-around">


        <div class="w-full">
            <h4 class="border-b-2 py-3 border-gray-300 font-semibold">O projekte</h4>
            <ul class="mt-2 space-y-1">
                <li><a href="{{ route('register') }}">Založiť svoj blog</a></li>
                <li><a href="{{ route('event.index') }}">Pridať novú akciu</a></li>
                <li><a href="#"><help-us></help-us></a></li>
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
            <form method="post" action="{{ route('messengers.store') }}" class="mt-4 relative">
                {{ csrf_field() }}
                <textarea class="border-2 border-gray-500 rounded-md w-full p-2" name="body" rows="3" class="w-full rounded-md" required placeholder="Napíšte nám svoje podnety ... Ďakujeme" value="{{ old('body') }}"></textarea>

                <div class="form-group">

                    @if(auth()->guest())
                    <label>Som človek  3+2 = </label>
                    <input class="px-2 rounded-sm" type="number" name="iamHuman" placeholder="Zadajte číslo 5" required>
                    @endif

                        <button type="submit" class="absolute right-0 px-2 p-1 border-2 text-sm rounded-sm mt-2 hover:bg-gray-700">Odoslať <span class="glyphicon glyphicon-envelope"></span></button>
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
    <div class="text-sm mb-2 flex place-content-center">Autor šablóny Gajdoš Gabriel 2018 <a href="{{ url('/') }}" title="Hlas Cirkvi.sk">Hlas Cirkvi.sk</a></div>

</footer>
