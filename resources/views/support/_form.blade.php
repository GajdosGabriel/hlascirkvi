{{-- Formulár novej správy podpore — v nástenke (Podpora). Päta má vlastný,
     tmavý, s rovnakými poľami. --}}
<form method="POST" action="{{ route('profile.support.store') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="page_url" value="{{ old('page_url', url()->previous()) }}">

    <div>
        <label class="ar-label" for="{{ $idPrefix }}-subject">Predmet</label>
        <input id="{{ $idPrefix }}-subject" name="subject" type="text" minlength="3" maxlength="150" required
               value="{{ old('subject') }}" class="ar-field" @error('subject') aria-invalid="true" @enderror>
        @error('subject')<p class="ar-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="ar-label" for="{{ $idPrefix }}-body">Správa</label>
        <textarea id="{{ $idPrefix }}-body" name="body" rows="5" minlength="3" maxlength="5000" required
                  class="ar-field" @error('body') aria-invalid="true" @enderror
                  placeholder="S čím vám môžeme pomôcť?">{{ old('body') }}</textarea>
        @error('body')<p class="ar-error">{{ $message }}</p>@enderror
    </div>

    <button type="submit" class="ar-btn ar-btn--accent">
        <i class="ph ph-paper-plane-tilt"></i> Odoslať
    </button>
</form>
