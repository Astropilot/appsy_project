<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Component\I18n;

class I18nTest extends TestCase {

    public function testLangFromURL() {
        $i18n = I18n::getInstance('tests/app/langs_test/', 'en');

        $url = $i18n->setLangFromURL('/dashboard/samplepage');
        $this->assertSame('en', $_SESSION['lang']);
        $this->assertSame('/dashboard/samplepage', $url);

        $url = $i18n->setLangFromURL('/fr/dashboard/samplepage');
        $this->assertSame('fr', $_SESSION['lang']);
        $this->assertSame('/dashboard/samplepage', $url);

        $url = $i18n->setLangFromURL('/en/dashboard/samplepage');
        $this->assertSame('en', $_SESSION['lang']);
        $this->assertSame('/dashboard/samplepage', $url);

        unset($_SESSION['lang']);
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr;q=0.9, en;q=0.8';
        $url = $i18n->setLangFromURL('/dashboard/samplepage');
        $this->assertSame('fr', $_SESSION['lang']);
    }

    public function testTranslate() {
        $i18n = I18n::getInstance('tests/app/langs_test/', 'en');

        $i18n->setLangFromURL('/dashboard/samplepage');
        $this->assertSame('TRANSLATION_NOT_FOUND', $i18n->translate('NOT_EXIST'));
        $this->assertSame('English', $i18n->translate('LANGUAGE_NAME'));

        $i18n->setLangFromURL('/fr/dashboard/samplepage');
        $this->assertSame('TRANSLATION_NOT_FOUND', $i18n->translate('NOT_EXIST'));
        $this->assertSame('Français', $i18n->translate('LANGUAGE_NAME'));

        $this->assertSame('English', $i18n->translate('LANGUAGE_NAME', 'en'));
    }

    public function testComputeTranslation() {
        $i18n = I18n::getInstance('tests/app/langs_test/', 'en');
        $context = array();

        $i18n->setLangFromURL('/dashboard/samplepage');
        $this->assertSame('TRANSLATION_NOT_FOUND', $i18n->computeTranslations("{{ translate 'NOT_EXIST' }}", $context));
        $this->assertArrayHasKey('lang', $context);
        $this->assertSame('en', $context['lang']);

        $context = array();

        $this->assertSame('English', $i18n->computeTranslations("{{ translate 'LANGUAGE_NAME' }}", $context));
        $this->assertArrayHasKey('lang', $context);
        $this->assertSame('en', $context['lang']);

        $this->assertSame('Français', $i18n->computeTranslations("{{ translate 'LANGUAGE_NAME' }}", $context, 'fr'));
        $this->assertArrayHasKey('lang', $context);
        $this->assertSame('fr', $context['lang']);
    }

    public function testSetLangToContext() {
        $i18n = I18n::getInstance('tests/app/langs_test/', 'en');
        $context = null;

        $i18n->setLangToContext($context, 'en');
        $this->assertArrayHasKey('lang', $context);
        $this->assertSame('en', $context['lang']);

        $context = array();

        $i18n->setLangToContext($context, 'fr');
        $this->assertArrayHasKey('lang', $context);
        $this->assertSame('fr', $context['lang']);
    }

    protected function tearDown(): void {
        unset($_SESSION['lang']);
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }
}
