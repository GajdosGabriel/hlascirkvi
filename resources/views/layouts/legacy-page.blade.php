@extends('layouts.app')

@section('body-class', 'ar-body')

@section('headerCSS')
    @parent
    <style nonce="{{ csp_nonce() }}">
        .ar-document { max-width: 1152px; margin: 0 auto; padding: 3rem 1.25rem 4rem; color: var(--ar-ink); }
        .ar-document__header { margin-bottom: 2rem; }
        .ar-document__header h1 { margin-top: .65rem; font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 750; line-height: 1.15; }
        .ar-document__intro { margin-top: .75rem; color: var(--ar-ink-soft); line-height: 1.7; }
        .ar-document__card { background: #fff; border: 1px solid var(--ar-line); border-radius: 1rem; padding: clamp(1.25rem, 4vw, 2.5rem); }
        .ar-document__prose { max-width: 76ch; margin: 0 auto; line-height: 1.8; overflow-wrap: anywhere; }
        .ar-document__prose h2 { margin-top: 2rem; color: var(--ar-ink); font-size: 1.25rem; }
        .ar-document__prose p, .ar-document__prose address { color: var(--ar-ink-soft); }
        .ar-document__prose a { color: var(--ar-accent); text-decoration: underline; text-underline-offset: 3px; }
        .ar-document__prose ul { list-style: disc; padding-left: 1.5rem; }
        .ar-video-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
        .ar-video-card { overflow: hidden; background: #fff; border: 1px solid var(--ar-line); border-radius: 1rem; }
        .ar-video-card__content { padding: 1.5rem; }
        .ar-video-card h2 { font-size: 1.125rem; font-weight: 700; line-height: 1.4; }
        .ar-video-card time { display: block; margin-top: .5rem; color: var(--ar-ink-soft); font-size: .8125rem; }
        .ar-video-player { position: relative; aspect-ratio: 16 / 9; background: var(--ar-ink); overflow: hidden; border-radius: .75rem; }
        .ar-video-player iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
        .ar-document a:focus-visible { outline: 3px solid var(--ar-accent); outline-offset: 4px; }
        @media (max-width: 640px) { .ar-document { padding: 2rem 1rem 3rem; } .ar-video-grid { grid-template-columns: 1fr; } }
    </style>
@endsection
