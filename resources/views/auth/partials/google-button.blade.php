{{-- Oficiálne tlačidlo Google Identity Services (rovnaký postup ako v projekte
     event). Google vráti v prehliadači ID token, skript v layouts/auth ho vloží
     do formulára a odošle na AuthController@googleAuth. Bez GOOGLE_CLIENT_ID sa
     tlačidlo nezobrazí. $context: signin | signup (mení len text tlačidla). --}}
@if (config('services.google.client_id'))
    <form method="POST" action="{{ route('auth.google') }}" data-google-signin
          data-client-id="{{ config('services.google.client_id') }}"
          data-context="{{ $context ?? 'signin' }}">
        @csrf
        <input type="hidden" name="credential">
        <div class="ar-google" data-google-host></div>
        <p class="ar-error text-center" data-google-failed hidden>
            Prihlásenie cez Google sa teraz nedá načítať. Skúste to neskôr alebo použite e-mail.
        </p>
    </form>
@endif
