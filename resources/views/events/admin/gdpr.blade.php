<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>gdpr</title>

    {{--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">--}}

    <link href="{{ URL::asset('css/app.css') }}" rel="stylesheet">

    <!-- Fonts -->
    {{--<link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">--}}
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

    <style>
        body { font-family: DejaVu Sans, sans-serif !important; }

    </style>

</head>
<body>

<div class="container">

<h2 style="margin-bottom: 4rem; text-align: center">Súhlas zo spracovaním osobných údajov</h2>

<span style="font-weight: 600">Menovaný: {{ $user->fullname }}</span>

    @if(! empty($user->street))<span>{{ $user->street }},</span> @endif
    @if(! empty($user->city))<span>{{ $user->city }},</span> @endif
    @if(! empty($user->psct))<span>{{ $user->psc }},</span> @endif


<p style="font-size: 13px">V súlade s § 11 zákona č. 122/2013 Z. z. o ochrane osobných údajov v platnom znení (ďalej len „zákon
    o OOÚ“) menovaná dotknutá osoba poskytuje
    <span style="font-weight: 600">{{ $event->organization->title }}</span>
    @if(! empty($event->organization->street))<span>{{ $event->organization->street }},</span> @endif
    @if(! empty($event->organization->village->fullname ))<span>{{ $event->organization->village->fullname }},</span> @endif
    @if(! empty($event->organization->village->zip))<span>{{ $event->organization->village->zip }},</span> @endif
    (ďalej „organizácii“) spracováva osobné údaje.

    Dotknutá osoba (zákonný zástupca) udeľuje prevádzkovateľovi dobrovoľný súhlas na spracovanie svojich osobných
    údajov v informačnom systéme svojej organizácie. </p>

<p style="font-size: 12px">Za účelom uplatnenia práva na prístup k osobným údajom, ich opravu alebo výmazania či obmedzenia, alebo prenositeľnosti údajov,
    môžete vznieť námietky na adrese uvedelnej organizácie.
</p>

<p style="font-size: 13px">Dotknutá osoba prehlasuje, že dáva prevádzkovateľovi súhlas na spracovanie osobných údajov
    v rozsahu stanovenom zákona, podľa nariadenia nových podmienok GDPR.

    Dotknutá osoba má právo svoj súhlas kedykoľvek odvolať alebo zmeniť.
    Zmena súhlasu nemá vplyv na spracúvanie pred jeho zmenou/zrušením písomne alebo prostredníctvom tohto systému.
    Súhlas bol podpísaný elektronicky.
</p>


   <div style="font-size: 12px">Otlačok elektronický podpisu: <small>{{ $user->token }} {{ bin2hex(random_bytes(46)) }} {{ bin2hex(random_bytes(46)) }} </small></div>

        <p style="font-size: 12px; text-align: center">Súhlas udelený prostredníctvo portálu hlascirkvi.sk</p>
</div>

</body>
</html>

{{--Na základe § 13 ods. 1 písm. a) Zákona č. 18/2018 Z. z. o ochrane osobných údajov a  čl. 6 ods. 1 písm. a) nariadenia Európskeho parlamentu --}}
{{--a Rady (EÚ) 2016/679 z 27. apríla 2016 o ochrane fyzických osôb pri spracúvaní osobných údajov a o voľnom pohybe takýchto údajov, týmto udeľujem súhlas so spracúvaním uvedených osobných údajov v tomto formulári pre prevádzkovateľa--}}
{{--Centrum pre rodinu Sigord, Zlatá Baňa 134, 082 52, IČO: 420893361 pre účel zverejnenia všeobecných, nie detailných fotografií vyhotovených na podujatí na webovej stránke www.centrumsigord.sk.--}}

{{--Súhlas na spracúvanie osobných údajov pre všetky účely vymenované vyššie udeľujem na dobu neurčitú alebo do odvolania.--}}
{{--Beriem na vedomie, že svoj súhlas so spracovaním osobných údajov môžem kedykoľvek odvolať rovnakým spôsobom, akým bol súhlas udelený. --}}
{{--Odvolanie súhlasu nemá vplyv na zákonnosť spracúvania osobných údajov založenom na súhlase pred jeho odvolaním. Poskytnuté osobné údaje môžu byť ďalej spracované na archivačné a štatistické účely. --}}
{{--Osobné údaje spracúva prevádzkovateľ na základe prejavenému záujmu o aktivity prevádzkovateľa.--}}
{{--Beriem na vedomie, že mám právo požadovať od prevádzkovateľa prístup k poskytnutým osobným údajom, právo na opravu osobných údajov, právo na vymazanie osobných údajov, právo na obmedzenie spracúvania osobných údajov, právo namietať spracúvanie osobných údajov, právo na prenosnosť osobných údajov, právo podať návrh na začatie konania podľa Zákona 18/2018 Z. z.. Beriem na vedomie, že u prevádzkovateľa nedochádza k profilovaniu. --}}
{{--Beriem na vedomie, že kontaktná osoba pre poskytovanie informácií dotknutým osobám je vždy uvedená na webovom sídle prevádzkovateľa. Spracúvanie poskytnutých osobných údajov môže prevádzkovateľ vykonávať aj prostredníctvom ďalšieho sprostredkovateľa. --}}
{{--Beriem na vedomie, že osobné údaje nebudú poskytnuté iným príjemcom bez môjho súhlasu.--}}
