{{-- Keep this in the document head: Vue removes styles inside #app. --}}
<style nonce="{{ csp_nonce() }}">
.co-overview { display: grid; gap: 24px; min-width: 0; color: var(--ar-ink); }
.co-overview .ar-panel { border-radius: 16px; box-shadow: 0 3px 16px rgba(16, 24, 40, .035); }
.co-hero {
    display: flex; flex-direction: row; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 24px; padding: 30px;
    border-top: 3px solid var(--ar-accent);
    background: linear-gradient(115deg, var(--ar-accent-soft), #fff 65%);
}
.co-identity { display: flex; align-items: center; gap: 20px; min-width: 0; flex: 1 1 340px; }
.co-avatar { width: 84px; height: 84px; flex: 0 0 84px; border-radius: 18px; font-size: 26px; box-shadow: 0 0 0 5px #fff; }
.co-heading { min-width: 0; }
.co-heading h2 { margin: 8px 0; font-size: clamp(23px, 3vw, 32px); font-weight: 800; line-height: 1.2; letter-spacing: -.035em; overflow-wrap: anywhere; }
.co-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ar-ink-soft); }
.co-muted { color: var(--ar-ink-soft); font-size: 13px; line-height: 1.65; }
.co-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
.co-heading .co-actions { margin-top: 14px; }
.co-overview .ar-btn { min-height: 40px; padding: 10px 16px; white-space: normal; text-align: center; }
.co-overview .ar-btn:not(.ar-btn--accent) { border-color: var(--ar-line); background: #fff; }
.co-overview .ar-btn:not(.ar-btn--accent):hover { border-color: var(--ar-accent); color: var(--ar-accent); background: var(--ar-accent-soft); }
.co-overview .ar-badge { padding: 5px 10px; white-space: normal; line-height: 1.4; }
.co-overview :is(a, button):focus-visible { outline: 2px solid var(--ar-accent); outline-offset: 3px; }
.co-metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
.co-metric { position: relative; padding: 22px; display: flex; flex-direction: column; gap: 10px; min-width: 0; }
.co-metric-label { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: 13px; font-weight: 600; }
.co-metric-label i { display: grid; place-items: center; flex: 0 0 36px; height: 36px; border-radius: 11px; background: var(--ar-accent-soft); color: var(--ar-accent); font-size: 21px; }
.co-metric strong { font-size: 36px; line-height: 1.15; font-weight: 800; letter-spacing: -.04em; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
.co-metric .co-muted { font-size: 12px; }
.co-columns { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr); gap: 24px; align-items: start; }
.co-stack { display: grid; gap: 24px; min-width: 0; }
.co-panel { padding: 24px; min-width: 0; }
.co-panel h3 { display: flex; align-items: center; gap: 10px; margin: 0 0 16px; padding-bottom: 16px; border-bottom: 1px solid var(--ar-line); font-size: 16px; line-height: 1.4; font-weight: 700; }
.co-panel h3 > i { color: var(--ar-accent); font-size: 21px; }
.co-panel h3 .ar-badge { margin-left: auto; }
.co-facts { margin: 0; }
.co-facts > div { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr); align-items: baseline; gap: 20px; padding: 13px 0; border-bottom: 1px solid var(--ar-line); font-size: 13px; line-height: 1.6; }
.co-facts > div:last-child { border-bottom: 0; padding-bottom: 0; }
.co-facts dt { color: var(--ar-ink-soft); }
.co-facts dd { margin: 0; text-align: right; font-weight: 600; min-width: 0; overflow-wrap: anywhere; font-variant-numeric: tabular-nums; }
.co-facts small { display: block; margin-top: 3px; font-weight: 400; color: var(--ar-ink-soft); }
.co-overview .ar-link { color: var(--ar-accent); text-underline-offset: 3px; }
.co-overview .ar-link:hover { text-decoration: underline; }
.co-divider { border-top: 1px solid var(--ar-line); padding-top: 18px; margin-top: 20px; }
.co-switch { margin-top: 20px; padding: 18px; border: 1px solid var(--ar-line); border-radius: 12px; background: var(--ar-paper); font-size: 13px; }
.co-switch p { margin: 0 0 14px; color: var(--ar-ink-soft); line-height: 1.65; }
.co-description { font-size: 14px; line-height: 1.8; overflow-wrap: anywhere; }
.co-description :is(p, ul, ol, blockquote) + :is(p, ul, ol, blockquote) { margin-top: 12px; }
.co-description :is(h2, h3, h4) { display: block; margin: 18px 0 8px; padding: 0; border: 0; font-size: 16px; font-weight: 700; }
.co-description ul { list-style: disc; padding-left: 22px; }
.co-description ol { list-style: decimal; padding-left: 22px; }
.co-description li + li { margin-top: 5px; }
.co-description a { color: var(--ar-accent); text-decoration: underline; text-underline-offset: 3px; }
.co-description blockquote { border-left: 3px solid var(--ar-accent); padding-left: 16px; color: var(--ar-ink-soft); }
.co-description img { max-width: 100%; height: auto; border-radius: 10px; }
.co-description table { display: block; max-width: 100%; overflow-x: auto; }
.co-managers { display: grid; gap: 16px; list-style: none; padding: 0; margin: 0; }
.co-managers li { display: flex; align-items: center; gap: 12px; overflow-wrap: anywhere; }
.co-managers li > div { min-width: 0; }
.co-managers strong { display: block; font-size: 13px; }
.co-managers a { display: block; margin-top: 4px; font-size: 12px; }
.co-person { display: grid; place-items: center; width: 40px; height: 40px; flex-shrink: 0; border-radius: 12px; background: var(--ar-paper); color: var(--ar-ink-soft); font-size: 20px; }
@media (max-width: 1100px) {
    .co-columns { grid-template-columns: minmax(0, 1fr); }
    .co-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 600px) {
    .co-overview, .co-columns, .co-stack { gap: 16px; }
    .co-hero, .co-panel { padding: 20px; }
    .co-identity { flex-basis: 100%; align-items: flex-start; gap: 14px; }
    .co-avatar { width: 56px; height: 56px; flex-basis: 56px; border-radius: 12px; font-size: 19px; box-shadow: 0 0 0 3px #fff; }
    .co-heading h2 { font-size: 23px; }
    .co-hero > .co-actions { width: 100%; }
    .co-hero > .co-actions .ar-btn { flex: 1 1 160px; }
    .co-metrics { gap: 12px; }
    .co-metric { padding: 16px; }
    .co-metric strong { font-size: 30px; }
    .co-metric-label { flex-wrap: wrap; font-size: 12px; }
    .co-metric-label i { flex-basis: 30px; height: 30px; font-size: 18px; }
    .co-facts > div { gap: 12px; }
}
@media (max-width: 380px) {
    .co-facts > div { grid-template-columns: minmax(0, 1fr); gap: 3px; }
    .co-facts dd { text-align: left; }
}
</style>
