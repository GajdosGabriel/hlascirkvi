@extends('layouts.app')

@section('body-class', 'ar-body')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Modlitebný múr',
        'description' => 'Miesto, kde sa dá poprosiť o modlitbu a modliť sa za prosby ostatných. '
            . 'Modlitebný múr Hlasu Cirkvi.',
        'jsonld' => [
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                ['Modlitebný múr', route('modlitby.index')],
            ]),
        ],
    ];
@endphp

@section('headerCSS')
    {{-- Rovnaké písmo ako zvyšok verejnej časti; layout ho nenačítava
         globálne, staršie sekcie ostávajú na Robote. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
@endsection

@section('content')

    {{-- Hlavička stránky. Tlačidlo je Vue komponent, lebo formulárové okno
         sa otvára cez zbernicu udalostí — obyčajný onclick v šablóne by
         Vue pri pripojení #app zahodilo. --}}
    <header class="border-b border-[color:var(--ar-line)] bg-white">
        <div class="mx-auto max-w-6xl px-4 py-7 md:py-10">
            <div class="gap-8 md:flex md:items-end md:justify-between">

                <div class="max-w-2xl">
                    <h1 class="ar-display text-2xl font-extrabold leading-tight md:text-[2.1rem]">
                        Modlitebný múr
                    </h1>

                    <p class="mt-2 text-sm text-gray-500 md:text-base">
                        Napíšte svoju prosbu a ostatní sa k nej pridajú v modlitbe.
                        Za úmysly, ktoré vás oslovia, sa môžete modliť aj vy.
                    </p>
                </div>

                <div class="mt-5 shrink-0 md:mt-0">
                    <new-prayer-button></new-prayer-button>
                </div>
            </div>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">
        {{-- Bez min-w-0 by dlhé slovo v prosbe roztiahlo stĺpec nad jeho
             podiel a stránka by sa na mobile posúvala doprava. --}}
        <div class="grid gap-10 lg:grid-cols-12">

            <div class="min-w-0 lg:col-span-7">
                <prayers-index-page></prayers-index-page>
            </div>

            <div class="min-w-0 lg:col-span-5">
                <prayers-index-page2></prayers-index-page2>
            </div>
        </div>
    </div>

@endsection
