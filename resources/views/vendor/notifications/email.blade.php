{{-- Šablóna všetkých notifikácií. Obsah skladá App\Notifications\Messages\PortalMail,
     vzhľad drží téma hlascirkvi (config/mail.php). --}}
<x-mail::message :unsubscribe-url="$unsubscribeUrl ?? null">
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@elseif ($level === 'error')
# Ups, niečo sa pokazilo
@else
# Dobrý deň,
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<x-mail::button :url="$actionUrl" :color="in_array($level, ['success', 'error']) ? $level : 'primary'">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
{{ rtrim($salutation ?: 'S pozdravom', ',') }},<br>
tím HlasCirkvi.sk

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
Ak tlačidlo „{{ $actionText }}" nefunguje, skopírujte do prehliadača tento odkaz:
<span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
