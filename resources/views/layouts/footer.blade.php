<footer>



    <div style="max-width: 114rem; margin: 0 auto" class="footer-body">


        <div class="footer-body__part">
            <h4>O projekte</h4>
            <ul>
                <li><a href="{{ route('register') }}">Založiť svoj blog</a></li>
                <li><a href="{{ route('event.index') }}">Pridať novú akciu</a></li>
                <li><a href="#"><help-us></help-us></a></li>
                <li><a href="{{ route('gdpr') }}">Ochrana osobných údajov</a></li>
            </ul>
        </div>

        <div class="footer-body__part">
            <h4>Kontakt</h4>
            <ul>
                <li><span class="fa fa-globe"></span>Trenčín, Slovensko</li>
                <li><span class="fa fa-phone"></span>0905 320 616</li>
                {{--<p><span class="fa fa-envelope"></span><a href="#" data-toggle="modal" data-target="#contact" data-whatever="@mdo" >Napíšte nám</a></p>--}}
            </ul>

        </div>

        <div class="footer-body__part">
            <h4>Napíšte nám</h4>
            <form method="post" action="{{ route('messengers.store') }}">
                {{ csrf_field() }}
                <textarea name="body" rows="5" style="opacity: .9;width: 100%; color: black; padding: .7rem" required placeholder="Napíšte nám svoje podnety ... Ďakujeme" value="{{ old('body') }}"></textarea>

                <div class="form-group">

                    @if(auth()->guest())
                    <label style="color: rgba(196, 198, 223, 0.6)">Som človek  3+2 = </label>
                    <input type="number" name="iamHuman" placeholder="Zadajte číslo 5" style="opacity: .85;color: black; width: 35%" required>
                    @endif

                        <button style="float: right" type="submit" class="btn btn-small">Odoslať <span class="glyphicon glyphicon-envelope"></span></button>
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

    <div class="footer-body__sub">Autor šablóny <span class="author">Gajdoš Gabriel</span> 2018 <a href="{{ url('/') }}" title="Hlas Cirkvi.sk">Hlas Cirkvi.sk</a></div>


</footer>
