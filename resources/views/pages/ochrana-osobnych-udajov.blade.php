@extends('layouts.app')

@php
    $seo = [
        'title' => 'Ochrana osobných údajov',
        'description' => 'Informácie o osobných údajoch a súkromí na portáli Hlas Cirkvi.',
    ];
@endphp

@section('content')
    <div class="page">
        <div class="space-y-5 max-w-4xl">
            <h1 class="page_title">Ochrana osobných údajov</h1>
            <p>
                Hlas Cirkvi (hlascirkvi.sk) je kresťanský informačný a komunitný portál. Sprístupňuje články,
                videá, prednášky, modlitbové úmysly a informácie o podujatiach. Používatelia môžu spravovať
                vlastný kanál, publikovať obsah, zapájať sa do diskusií a sledovať obľúbené kanály.
                Tu vysvetľujeme, aké osobné údaje pri týchto činnostiach spracúvame a ako môžete uplatniť svoje práva.
            </p>

            <h2 class="text-xl font-semibold">Prevádzkovateľ a kontakt</h2>
            <address class="not-italic">
                <strong>Gabriel Gajdoš</strong><br>
                Východná 3, 911 08 Trenčín, Slovensko<br>
                E-mail: <a href="mailto:admin@hlascirkvi.sk" class="underline">admin@hlascirkvi.sk</a><br>
                Telefón: <a href="tel:+421905320616" class="underline">+421 905 320 616</a>
            </address>
            <p>
                Otázky o súkromí a žiadosti o opravu či výmaz údajov môžete poslať priamo e-mailom,
                aj bez používateľského účtu. Po prihlásení môžete použiť aj
                <a href="#napiste-nam" class="underline">kontaktný formulár v pätičke</a>.
                Spracúvanie sa riadi
                <a href="https://eur-lex.europa.eu/legal-content/SK/TXT/?uri=CELEX:32016R0679" class="underline" target="_blank" rel="noopener noreferrer">nariadením (EÚ) 2016/679 (GDPR)</a>
                a zákonom č. 18/2018 Z. z. o ochrane osobných údajov.
            </p>

            <h2 class="text-xl font-semibold">Aké údaje spracúvame a prečo</h2>
            <ul class="list-disc pl-6 space-y-3">
                <li><strong>Registrácia a účet:</strong> meno, priezvisko, e-mail, heslo uložené vo forme jednosmerného odtlačku,
                    overenie adresy a nastavenia účtu. Slúžia na registráciu, prihlásenie, obnovu prístupu a správu kanála.
                    Právnym základom je poskytovanie služby na vašu žiadosť (čl. 6 ods. 1 písm. b) GDPR).
                    Bez povinných údajov účet nemožno vytvoriť.</li>
                <li><strong>Profil a kanál:</strong> obrázok, popis, názov, zameranie a kontakty, ktoré doplníte.
                    Používame ich na správu a prezentáciu kanála v rámci služby. Pri osobnom kanáli môžu identifikovať autora.</li>
                <li><strong>Príspevky, komentáre a modlitby:</strong> text, označenie autora, dátum a väzba na účet či kanál.
                    Slúžia na zverejnenie obsahu, o ktoré žiadate, a vedenie diskusie. Moderovanie a ochrana pred spamom
                    vychádzajú z oprávneného záujmu na bezpečnej komunikácii (čl. 6 ods. 1 písm. f) GDPR).</li>
                <li><strong>Obľúbené kanály a upozornenia:</strong> sledovanie kanálov a nastavenia upozornení
                    spracúvame na poskytovanie funkcií, ktoré si zvolíte.</li>
                <li><strong>Správy a podnety:</strong> obsah a údaje odosielateľa používame na vybavenie požiadavky.
                    Správy správcovi portálu sa ukladajú v portáli. Správa cez verejnú stránku kanála sa doručí
                    e-mailom príjemcovi spolu s vaším menom a e-mailom pre odpoveď; jej text sa v portáli neukladá.
                    Príjemca ďalej zodpovedá za spracúvanie doručenej správy.</li>
                <li><strong>Technické údaje:</strong> IP adresy, údaje o prihlásení a záznamy udalostí používame
                    na ochranu účtov, obmedzenie zneužívania a riešenie chýb. Pri štatistikách zobrazení používame
                    pseudonymný odtlačok návštevníka. Právnym základom je oprávnený záujem na bezpečnosti
                    a vyhodnocovaní používania portálu.</li>
            </ul>

            <h2 class="text-xl font-semibold">Verejný obsah a citlivé informácie</h2>
            <p>
                Zverejnené profily, kanály, príspevky, komentáre a modlitbové úmysly si môžu prečítať aj neprihlásení
                návštevníci a môžu ich indexovať vyhľadávače. Prihlasovací e-mail, heslo a bezpečnostné IP adresy
                nie sú súčasťou verejného profilu. Kontakty kanála používané na doručovanie správ sa na jeho
                verejnej stránke nezobrazujú.
            </p>
            <p>
                Modlitby, svedectvá alebo údaje o vierovyznaní môžu odhaľovať náboženské presvedčenie či zdravotný stav.
                Ide o osobitne chránené údaje. Zverejňujte iba informácie, ktoré chcete sami vedome sprístupniť
                verejnosti; pri takomto zverejnení môže byť splnená podmienka podľa čl. 9 ods. 2 písm. e) GDPR.
                Samotné používanie portálu nepredstavuje výslovný súhlas so spracúvaním všetkých citlivých údajov.
                Neuvádzajte identifikovateľné citlivé údaje o iných osobách, najmä o deťoch; úmysel možno napísať
                aj bez mien a podrobností.
            </p>

            <h2 class="text-xl font-semibold">E-mailové správy a odber noviniek</h2>
            <p>
                Posielame správy potrebné na používanie účtu, napríklad overenie adresy, obnovu hesla a potvrdenie
                odoslaného obsahu. Odber noviniek portálu sa riadi nastavením vo vašom účte. Vypnúť ho môžete cez
                odhlasovací odkaz v newsletteri alebo v
                <a href="{{ route('newsletter.preferences') }}" class="underline">nastavení odberu</a>.
                Odhlásenie nebráni doručovaniu správ potrebných na správu účtu. Záznam o odhlásení obsahuje dátum
                a IP adresu a zostáva pri účte do obnovenia odberu alebo vybavenia výmazu, aby sme rešpektovali
                vašu voľbu. Pri novom dobrovoľnom prihlásení na odber je právnym základom súhlas
                (čl. 6 ods. 1 písm. a) GDPR).
            </p>

            <h2 class="text-xl font-semibold">Ako dlho údaje uchovávame</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li>Údaje účtu a nastavenia uchovávame počas jeho používania. O zrušenie účtu a výmaz osobných údajov môžete požiadať prevádzkovateľa.</li>
                <li>Verejný obsah uchovávame, kým je publikovaný. Pri výmaze posúdime aj odstránenie alebo anonymizáciu údajov autora a ochranu práv ďalších účastníkov diskusie.</li>
                <li>Nepotvrdené registrácie, komentáre, modlitby a sledovania kanálov vrátane IP adresy sa odstraňujú po potvrdení alebo po uplynutí platnosti odkazu, štandardne po 7 dňoch. Expirované záznamy odstraňuje denná údržba.</li>
                <li>IP adresu posledného prihlásenia odstraňujeme dennou údržbou po 90 dňoch od posledného prihlásenia.</li>
                <li>Systémový denník udalostí uchovávame štandardne 31 dní, varovania a chyby 90 dní.</li>
                <li>Pseudonymné záznamy zobrazení odstraňujeme dennou údržbou po 90 dňoch; súhrnné počty zobrazení môžu zostať zachované.</li>
                <li>Správy správcovi uchovávame počas vybavovania podnetu a následne len v rozsahu potrebnom na doloženie jeho vybavenia alebo ochranu právnych nárokov.</li>
            </ul>
            <p>
                Ak ďalšie uchovanie vyžaduje zákon alebo ochrana právnych nárokov, obmedzíme ho na potrebný rozsah
                a čas. Výmaz z aktívneho systému nemusí znamenať okamžité odstránenie zo záloh alebo kópií
                vo vyhľadávačoch a u iných príjemcov. Pri vybavení žiadosti vysvetlíme rozsah a prípadné obmedzenia.
            </p>

            <h2 class="text-xl font-semibold">Cookies a vložené služby</h2>
            <p>
                Používame nevyhnutné cookies na prihlásenie, udržanie relácie a ochranu formulárov.
                Pri zapamätaní prihlásenia sa používa aj cookie na túto funkciu. Cookies môžete obmedziť
                v prehliadači; prihlásenie a niektoré formuláre potom nemusia fungovať. Voliteľné analytické
                cookies možno používať až po vašom súhlase, ktorý môžete odmietnuť alebo odvolať.
            </p>
            <p>
                Videá a ďalší vložený obsah môžu nadviazať spojenie s externým poskytovateľom, ktorý dostane
                napríklad IP adresu a údaje o prehliadači a môže používať vlastné cookies. Pri prihlásení cez
                Google alebo Facebook získavame údaje sprístupnené poskytovateľom na vytvorenie alebo prepojenie
                účtu, najmä identifikátor, meno, e-mail a profilový obrázok. Tieto služby majú vlastné pravidlá:
                <a href="https://policies.google.com/privacy" class="underline" target="_blank" rel="noopener noreferrer">Google a YouTube</a>
                a <a href="https://www.facebook.com/privacy/policy/" class="underline" target="_blank" rel="noopener noreferrer">Facebook</a>.
                Portál používa aj YouTube API; na používanie YouTube sa vzťahujú jeho
                <a href="https://www.youtube.com/t/terms" class="underline" target="_blank" rel="noopener noreferrer">podmienky používania</a>.
            </p>

            <h2 class="text-xl font-semibold">Príjemcovia údajov a umelá inteligencia</h2>
            <p>
                K údajom pristupuje prevádzkovateľ a poverené osoby v rozsahu potrebnom na správu, podporu
                a moderovanie portálu. Technické spracúvanie zabezpečujú aj poskytovatelia hostingu
                a e-mailových služieb. Údaje môžeme poskytnúť príslušným orgánom, ak to vyžaduje zákon.
            </p>
            <p>
                Portál využíva služby OpenAI na spracovanie obsahu, napríklad zhrnutia a návrhy odpovedí v diskusiách.
                Do služby sa preto môže odoslať text príspevku alebo komentára a uvedené meno autora.
                Chýbajúce údaje kanálov môžeme vyhľadávať vo verejných zdrojoch s pomocou AI. Správcovi kanála
                oznámime doplnenie e-mailom; údaje môže upraviť alebo požiadať o odstránenie. Pri údajoch
                získaných z verejných zdrojov je právnym základom oprávnený záujem na aktuálnom katalógu
                kanálov pri rešpektovaní práv dotknutých osôb. Informácie o poskytovateľovi nájdete v
                <a href="https://openai.com/policies/privacy-policy/" class="underline" target="_blank" rel="noopener noreferrer">zásadách ochrany súkromia OpenAI</a>.
            </p>
            <p>
                Externé služby môžu spracúvať údaje aj mimo Európskeho hospodárskeho priestoru. Prenos musí
                spĺňať podmienky GDPR, napríklad rozhodnutie o primeranosti alebo štandardné zmluvné doložky
                a potrebné doplňujúce opatrenia. Informácie o príjemcoch a zárukách pre konkrétne spracúvanie
                si môžete vyžiadať od prevádzkovateľa. Portál nevykonáva rozhodovanie založené výlučne
                na automatizovanom spracúvaní, ktoré by malo voči vám právne alebo podobne významné účinky.
            </p>

            <h2 class="text-xl font-semibold">Zabezpečenie</h2>
            <p>
                Prístup k údajom obmedzujeme podľa oprávnení, heslá ukladáme vo forme jednosmerných odtlačkov
                a používame ochranu formulárov a obmedzenia proti zneužívaniu. Bezpečnostné opatrenia
                priebežne prispôsobujeme rizikám a rozsahu spracúvania.
            </p>

            <h2 class="text-xl font-semibold">Vaše práva</h2>
            <p>
                Za podmienok GDPR môžete žiadať prístup k údajom, opravu, výmaz, obmedzenie spracúvania alebo
                prenositeľnosť. Proti spracúvaniu na základe oprávneného záujmu môžete namietať. Súhlas môžete odvolať
                bez vplyvu na zákonnosť predchádzajúceho spracúvania. Žiadosť vybavíme spravidla do jedného mesiaca;
                pri zložitej žiadosti v tejto lehote oznámime prípadné predĺženie najviac o ďalšie dva mesiace
                a jeho dôvod. Pri dôvodných pochybnostiach o totožnosti môžeme požiadať o primerané overenie.
            </p>
            <p>
                Máte právo podať návrh na začatie konania na
                <a href="https://www.dataprotection.gov.sk/sk/urad/konanie-ochrane-osobnych-udajov/" class="underline" target="_blank" rel="noopener noreferrer">Úrad na ochranu osobných údajov Slovenskej republiky</a>.
                Predchádzajúci kontakt s prevádzkovateľom nie je podmienkou uplatnenia tohto práva.
            </p>
            <p class="text-sm">Posledná aktualizácia: 4. októbra 2026.</p>
        </div>
    </div>
@endsection

@section('aside')
@endsection
