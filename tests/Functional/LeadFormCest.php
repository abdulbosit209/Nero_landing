<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\tests\Support\FunctionalTester;

final class LeadFormCest
{
    public function _before(FunctionalTester $I)
    {
        $I->amOnRoute('site/index');
    }

    public function seeLeadForm(FunctionalTester $I)
    {
        $I->seeElement('#lead-form-form');
    }

    public function submitEmptyForm(FunctionalTester $I)
    {
        $I->submitForm('#lead-form-form', []);
        $I->expectTo('see validation errors');
        $I->seeElement('#lead-form-form');
        $I->see('Name cannot be blank');
        $I->see('Phone Number cannot be blank');
        $I->see('Service cannot be blank');
    }

    public function submitFormWithInvalidPhone(FunctionalTester $I)
    {
        $I->submitForm('#lead-form-form', [
            'LeadForm[name]' => 'tester',
            'LeadForm[phoneNumber]' => '1234',
            'LeadForm[service]' => 'dent',
        ]);
        $I->expectTo('see a phone format error');
        $I->seeElement('#lead-form-form');
        $I->see('Enter a valid phone number');
        $I->dontSee('Name cannot be blank');
    }

    public function submitFormSuccessfully(FunctionalTester $I)
    {
        $I->submitForm('#lead-form-form', [
            'LeadForm[name]' => 'tester',
            'LeadForm[phoneNumber]' => '+998901234567',
            'LeadForm[service]' => 'dent',
            'LeadForm[description]' => 'Small dent on the rear door.',
        ]);
        $I->see('We call you later');
        $I->seeElement('#lead-form-form');
    }

    // Only a single file: Codeception's InnerBrowser::attachFile() operates on one
    // Symfony FileFormField per call, so it can't simulate a real browser attaching
    // several files to one <input multiple> in one go. Multi-photo delivery (including
    // Telegram's sendMediaGroup path) is verified manually against the running app —
    // see the project notes on the TelegramNotifier/LeadForm fixes.
    public function submitFormWithPhoto(FunctionalTester $I)
    {
        $I->amOnRoute('site/index');
        $I->attachFile('#leadform-photos', 'sample.jpg');
        $I->fillField('LeadForm[name]', 'tester');
        $I->fillField('LeadForm[phoneNumber]', '+998901234567');
        $I->selectOption('LeadForm[service]', 'paint');
        $I->click('button[type=submit]', '#lead-form-form');
        $I->see('We call you later');
        $I->seeElement('#lead-form-form');
    }
}
