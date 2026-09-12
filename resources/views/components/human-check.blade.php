{{-- Neviditeľná kontrola „nie som robot" — vysvetlenie v App\Support\HumanCheck.
     Pasca nesmie byť display:none ani hidden: automaty také polia preskakujú,
     zato odsunuté pole vyplnia. Preto je len mimo viditeľnej plochy stránky. --}}
<div aria-hidden="true" style="position:absolute;left:-9999px;top:0;width:1px;height:1px;overflow:hidden">
    <label for="{{ \App\Support\HumanCheck::TRAP }}">Nechajte prosím prázdne</label>
    <input type="text"
           id="{{ \App\Support\HumanCheck::TRAP }}"
           name="{{ \App\Support\HumanCheck::TRAP }}"
           value=""
           tabindex="-1"
           autocomplete="off">
</div>

<input type="hidden" name="{{ \App\Support\HumanCheck::STAMP }}" value="{{ \App\Support\HumanCheck::stamp() }}">
