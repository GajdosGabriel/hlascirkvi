@props(['url'])
{{-- Značka portálu. Obrázok je apple-touch-icon.png (PNG, nie SVG — Gmail
     ani Outlook SVG nezobrazia), text vedľa neho ostane čitateľný aj pri
     vypnutých obrázkoch. --}}
<tr>
<td class="header">
<a href="{{ $url }}">
<img src="{{ asset('apple-touch-icon.png') }}" class="brand-logo" width="40" height="40" alt="">
<span class="brand-name">Hlas Cirkvi</span>
</a>
<p class="brand-tagline">Kresťanský portál</p>
</td>
</tr>
