<?php

namespace Tests\Unit;

use App\Services\Prayers\SpamDetector;
use PHPUnit\Framework\TestCase;

/**
 * Detekcia spamu nad prosbami o modlitbu.
 *
 * Podstatná je tu druhá polovica testov: slová „pôžička", „urgentne" aj
 * „pozícia" sa v skutočných prosbách bežne vyskytujú, takže falošný poplach
 * je horší než prepustený spam — zmazal by cudziu prosbu o modlitbu.
 */
class PrayerSpamDetectorTest extends TestCase
{
    private SpamDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new SpamDetector;
    }

    public function testLoanOfferIsSpam()
    {
        $hit = $this->detector->inspect(
            'Získajte svoju urgentnú pôžičku',
            'Pôžičky pre každého. Pôžička ponúka prijateľnú úrokovú sadzbu 2 %. '
            . 'Potrebujete financovať konsolidáciu dlhu? Kontaktujte nás: kupka.josef@gmail.com'
        );

        $this->assertNotNull($hit);
        $this->assertSame(SpamDetector::REASON_LOAN, $hit['reason']);
    }

    public function testLoanOfferWithWhatsappIsSpam()
    {
        $this->assertTrue($this->detector->isSpam(
            'Prosba o modlitbu',
            'Ponúkame všetky druhy pôžičiek s nízkou úrokovou sadzbou 3%. '
            . 'Môžete sa prihlásiť cez whatsapp: +14092051142'
        ));
    }

    /**
     * Starší import nechal v texte `??` namiesto diakritiky. Spam sa tým
     * nestal menej spamom, len prestal byť nájditeľný cez „pôžičiek".
     */
    public function testLoanOfferWithBrokenDiacriticsIsSpam()
    {
        $this->assertTrue($this->detector->isSpam(
            '??verové ponuky medzi jednotlivcami vážne',
            'Mám kapitál na poskytovanie krátkodobých a dlhodobých pôži??iek od 50 000 EUR. '
            . '2% úrok ro??ne, aby táto pôži??ka bola seriózna. david.liska@email.cz'
        ));
    }

    public function testSpellCasterAdvertIsSpam()
    {
        $hit = $this->detector->inspect(
            'Prosba o modlitbu',
            'Chcem svetu povedať o mocnom zosielateľovi kúziel menom Dr. UDAMA ADA. '
            . 'Napíšte mu na udamaada@gmail.com alebo WhatsApp +2348054681416'
        );

        $this->assertNotNull($hit);
        $this->assertSame(SpamDetector::REASON_SCAM, $hit['reason']);
    }

    public function testBareLinkIsSpam()
    {
        $hit = $this->detector->inspect('Prosba o modlitbu', 'https://www. youtube. com/watch?v=tuqwE2ouR7E');

        $this->assertNotNull($hit);
        $this->assertSame(SpamDetector::REASON_LINK, $hit['reason']);
    }

    public function testBareEmailIsSpam()
    {
        $this->assertTrue($this->detector->isSpam('Prosba o modlitbu', 'Kontakt:janmotovsky333@gmail. com'));
    }

    public function testPrayerMentioningOwnLoanIsKept()
    {
        $this->assertFalse($this->detector->isSpam(
            'Prosba o modlitbu',
            'Milé sestričky prosím o modlitbu o pomoc pre moju dcéru, aby si R.G. prevzal svoju pôžičku od nej. PBZ'
        ));
    }

    public function testUrgentPrayerIsKept()
    {
        $this->assertFalse($this->detector->isSpam(
            'Prosba o modlitbu',
            'Urgentne prosím o modlitby za sestru a jej nenarodené dieťatko. Prosíme o zázrak. Ďakujeme'
        ));
    }

    public function testPrayerAboutJobApplicationIsKept()
    {
        $this->assertFalse($this->detector->isSpam(
            'Prosba o modlitbu',
            'Prosím o modlitby za môjho manžela, podal si žiadosť na istú pozíciu, aby ho na to miesto prijali.'
        ));
    }

    /**
     * Prosba, ktorá odkaz iba prikladá, nie je „príspevok bez textu".
     */
    public function testPrayerWithAttachedLinkIsKept()
    {
        $this->assertFalse($this->detector->isSpam(
            'Prosba o modlitbu',
            'https://www. minv. sk/?ptc prosím o modlitby o ochranu mojej celej rodiny'
        ));
    }

    /**
     * `\b` v regulárnom výraze berie za slovo len ASCII, takže „ďakujem.skúsim"
     * by sa dalo prečítať ako doména .sk a krátka prosba by zmizla.
     */
    public function testShortPrayerIsNotReadAsDomain()
    {
        $this->assertFalse($this->detector->isSpam('Prosba o modlitbu', 'Za zdravie mamy. Skúsim aj zajtra. PBZ'));
    }

    public function testShortPrayerWithoutLinkIsKept()
    {
        $this->assertFalse($this->detector->isSpam('Prosba o modlitbu', 'Prosím o modlitbu. PBZ'));
    }
}
