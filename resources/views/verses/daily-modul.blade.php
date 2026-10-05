<section class="rounded-xl border border-[color:var(--ar-line)] bg-white p-5">
    <h2 class="ar-kicker">Denné zamyslenie</h2>
    @if ($verse)
        <blockquote class="mt-4 leading-relaxed">
            <p>{{ $verse->biblicky_vers }}</p>
            <footer class="mt-2 text-sm text-[color:var(--ar-ink-soft)]">{{ $verse->biblicky_vers_ref }}</footer>
        </blockquote>
        <a class="ar-btn ar-btn--quiet mt-4" href="{{ url('zamyslenia') }}">Prečítať zamyslenie <span aria-hidden="true">→</span></a>
    @else
        <p class="mt-3 text-sm text-[color:var(--ar-ink-soft)]">Dnešné zamyslenie zatiaľ nie je dostupné.</p>
    @endif
</section>
