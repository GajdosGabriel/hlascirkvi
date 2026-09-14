<style>
    /* Spoločné formuláre administrácie a správy kanála vrátane Vue prvkov. */
    .ar-dash .form-control {
        max-width: 100%;
        border: 1px solid var(--ar-line);
        border-radius: .5rem;
        background: #fff;
        color: var(--ar-ink);
        font-size: .9375rem;
    }
    .ar-dash .form-control:focus {
        border-color: var(--ar-accent);
        box-shadow: 0 0 0 3px rgba(var(--ar-accent-rgb), .12);
        outline: none;
    }
    .ar-dash .form-category, .ar-dash .form-author { min-width: 0; }
    .ar-dash .form-group > label, .ar-dash .form-category > label, .ar-dash .form-author > label {
        display: block;
        margin-bottom: .35rem;
        color: var(--ar-ink);
        font-size: .8125rem;
        font-weight: 600;
    }
    .ar-dash .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        border: 1px solid var(--ar-line);
        border-radius: 9999px;
        padding: .5rem 1rem;
        background: #fff;
        color: var(--ar-ink);
        font-size: .8125rem;
        font-weight: 600;
    }
    .ar-dash .btn:hover { border-color: var(--ar-accent); color: var(--ar-accent); }
    .ar-dash .btn-primary { background: var(--ar-accent); border-color: var(--ar-accent); color: #fff; }
    .ar-dash .btn-primary:hover { background: #9f1717; color: #fff; }
    .ar-dash :is(a, button, input, select, textarea):focus-visible { outline: 2px solid var(--ar-accent); outline-offset: 3px; }
    .ar-workspace__content > form {
        border: 1px solid var(--ar-line);
        border-radius: .625rem;
        background: #fff;
        padding: clamp(1rem, 3vw, 1.5rem);
        margin-bottom: 1.5rem;
    }
    .ar-dash .card { border: 1px solid var(--ar-line); border-radius: .625rem; background: #fff; box-shadow: none; }
    .ar-dash .card_header { padding: .75rem 1rem; border-bottom: 1px solid var(--ar-line); background: transparent; color: var(--ar-ink-soft); }
    /* Formulár článku (posts/form). Nesmie byť v samotnej šablóne — <style>
       vnútri #app Vue zahodí. */
    .post-form__meta {
        display: grid;
        gap: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }
    @media (min-width: 768px) {
        .post-form__meta { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .post-form__meta .form-control { width: 100%; }
    .post-form__choice {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }
    .post-form__choice label {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        margin: 0;
        padding: .5rem .875rem;
        border: 1px solid var(--ar-line);
        border-radius: .5rem;
        background: #fff;
        font-size: .875rem;
        font-weight: 500;
        cursor: pointer;
    }
    .post-form__choice label:has(input:checked) {
        border-color: var(--ar-accent);
        background: rgba(var(--ar-accent-rgb), .06);
        color: var(--ar-accent);
    }
    /* mb-6 by .form-group z app.css (je za utilitami) prebil. */
    .post-form__images { margin-top: 1.25rem; margin-bottom: 1.5rem; }
    .post-form__section-label {
        display: block;
        margin-bottom: .35rem;
        color: var(--ar-ink);
        font-size: .8125rem;
        font-weight: 600;
    }
    .ar-admin__content { color: var(--ar-ink); }
    .ar-admin__table { overflow-x: auto; border: 1px solid var(--ar-line); border-radius: .625rem; background: #fff; }
    .ar-admin__table table { width: 100%; border: 0; border-collapse: collapse; font-size: .8125rem; }
    .ar-admin__table thead { background: var(--ar-paper); color: var(--ar-ink-soft); }
    .ar-admin__table th { padding: .75rem; text-align: left; font-weight: 600; white-space: nowrap; }
    .ar-admin__table td { padding: .65rem .75rem; vertical-align: top; border: 0; }
    .ar-admin__table tr { border: 0; border-bottom: 1px solid var(--ar-line); }
    .ar-admin__table tbody tr:last-child { border-bottom: 0; }
    .ar-admin__table tbody tr:hover { background: var(--ar-paper); }
    .ar-admin__content .card { border: 1px solid var(--ar-line); border-radius: .625rem; background: #fff; box-shadow: none; }
    .ar-admin__content .card_header { padding: .75rem 1rem; border-bottom: 1px solid var(--ar-line); color: var(--ar-ink-soft); }
    .ar-admin__content .form-control { max-width: 100%; }
</style>
