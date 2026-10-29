{{-- Formulár novej správy podpore — v nástenke (Podpora). Päta má vlastný,
     tmavý, s rovnakými poľami. --}}
<form method="POST" action="{{ route('profile.support.store') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="page_url" value="{{ old('page_url', url()->previous()) }}">

    <div class="grid gap-4 sm:grid-cols-[14rem_1fr]">
        <div>
            <label class="ar-label" for="{{ $idPrefix }}-category">Téma</label>
            <select id="{{ $idPrefix }}-category" name="category" class="ar-field" required>
                @foreach (\App\Enums\SupportCategory::options() as $value => $label)
                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="ar-label" for="{{ $idPrefix }}-subject">Predmet <span class="font-normal text-[color:var(--ar-muted)]">(nepovinné)</span></label>
            <input id="{{ $idPrefix }}-subject" name="subject" type="text" maxlength="150" value="{{ old('subject') }}"
                   class="ar-field" @error('subject') aria-invalid="true" @enderror>
            @error('subject')<p class="ar-error">{{ $message }}</p>@enderror
        </div>
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
