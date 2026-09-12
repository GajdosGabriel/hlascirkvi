{{-- Pripomienka nepotvrdenej e-mailovej adresy. Prístup na portál neblokuje,
     len drží možnosť poslať potvrdzovací e-mail znova na dosah — bez nej sa
     človek, ktorému e-mail nedorazil, k overeniu nemá ako vrátiť.
     Účty z Googlu a Facebooku majú adresu overenú od poskytovateľa, takže
     im sa pruh nikdy neukáže. --}}
@auth
    @if (! auth()->user()->hasVerifiedEmail())
        <div class="border-b" style="background: var(--ar-accent-soft, #fdf1f1); border-color: var(--ar-line, #e3e4e8)">
            <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-center gap-x-3 gap-y-1 px-4 py-2 text-sm">
                <span style="color: var(--ar-ink, #101828)">
                    Potvrďte si e-mailovú adresu <strong>{{ auth()->user()->email }}</strong>.
                </span>

                <a href="{{ route('verification.notice') }}" class="font-semibold underline"
                   style="color: var(--ar-accent, #b91c1c)">Ako na to</a>

                <form method="POST" action="{{ route('verification.resend') }}" class="inline">
                    @csrf
                    <button type="submit" class="font-semibold underline" style="color: var(--ar-accent, #b91c1c)">
                        Poslať e-mail znova
                    </button>
                </form>
            </div>
        </div>
    @endif
@endauth
