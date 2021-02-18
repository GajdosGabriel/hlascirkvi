<footer style="background: #1e2e37; color: #a6a6a6" class="p-5">



    <div  class="max-w-7xl mx-auto flex justify-around">


        <div class="">
            <h4 class="border-b-2 py-3 border-gray-300">O projekte</h4>
            <ul class="mt-2 space-y-1">
                <li><a href="{{ route('register') }}">Založiť svoj blog</a></li>
                <li><a href="{{ route('event.index') }}">Pridať novú akciu</a></li>
                <li><a href="#"><help-us></help-us></a></li>
                <li><a href="{{ route('gdpr') }}">Ochrana osobných údajov</a></li>
            </ul>
        </div>

        <div class="">
            <h4 class="border-b-2 py-3 border-gray-300">Kontakt</h4>
            <ul class="mt-2 space-y-1">
                <li><span class="fa fa-globe"></span>Trenčín, Slovensko</li>
                <li><span class="fa fa-phone"></span>0905 320 616</li>
                {{--<p><span class="fa fa-envelope"></span><a href="#" data-toggle="modal" data-target="#contact" data-whatever="@mdo" >Napíšte nám</a></p>--}}
            </ul>

        </div>

        <div class="">
            <h4 class="border-b-2 py-3 border-gray-300">Napíšte nám</h4>
            <form method="post" action="{{ route('messengers.store') }}" class="w-full">
                {{ csrf_field() }}
                <textarea name="body" rows="5" class="w-full rounded-md" required placeholder="Napíšte nám svoje podnety ... Ďakujeme" value="{{ old('body') }}"></textarea>

                <div class="form-group">

                    @if(auth()->guest())
                    <label style="color: rgba(196, 198, 223, 0.6)">Som človek  3+2 = </label>
                    <input type="number" name="iamHuman" placeholder="Zadajte číslo 5" style="opacity: .85;color: black; width: 35%" required>
                    @endif

                        <button style="float: right" type="submit" class="px-2 p-1 border-2 text-sm rounded-sm mt-2">Odoslať <span class="glyphicon glyphicon-envelope"></span></button>
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
    <div class="mx-auto m-5">Autor šablóny <span class="author">Gajdoš Gabriel</span> 2018 <a href="{{ url('/') }}" title="Hlas Cirkvi.sk">Hlas Cirkvi.sk</a></div>



</footer>
