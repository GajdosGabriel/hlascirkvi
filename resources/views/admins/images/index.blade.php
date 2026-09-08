@extends('layouts.admin')

@section('title')
    <title>Obrázky v koši</title>
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">Obrázky v koši</x-slot>
        <x-slot name="page">
            <section class="ar-panel">
                <div class="ar-panel__body">
                    <p class="text-sm text-[color:var(--ar-ink-soft)]">Počet odstránených obrázkov: {{ $images->count() }}</p>
                    @if ($images->isNotEmpty())
                        <form class="mt-4" method="POST" action="{{ route('admin.image.destroy', ['image' => 'trashed']) }}"
                              onsubmit="return confirm('Natrvalo vymazať všetky obrázky v koši? Túto akciu nie je možné vrátiť späť.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="ar-btn ar-btn--quiet">Natrvalo vymazať obrázky v koši</button>
                        </form>
                    @else
                        <p class="mt-3 text-sm">Kôš je prázdny.</p>
                    @endif
                </div>
            </section>
        </x-slot>
    </x-pages.admin>
@endsection
