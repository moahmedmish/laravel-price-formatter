<?php

namespace YourName\PriceFormatter\Tests;

use Orchestra\Testbench\TestCase;
use YourName\PriceFormatter\PriceFormatterServiceProvider;
use YourName\PriceFormatter\Facades\PriceFormatter;

class PriceFormatterTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [PriceFormatterServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'PriceFormatter' => PriceFormatter::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Make sure the config is loaded
        $this->app['config']->set('price-formatter', require __DIR__ . '/../config/price-formatter.php');
    }

    /** @test */
    public function it_formats_egyptian_pounds_in_english()
    {
        $formatted = PriceFormatter::format(5, 'EG', 'en');
        $this->assertEquals('5 LE', $formatted);
    }

    /** @test */
    public function it_formats_egyptian_pounds_in_arabic()
    {
        $formatted = PriceFormatter::format(5, 'EG', 'ar');
        $this->assertEquals('5 ج م', $formatted);
    }

    /** @test */
    public function it_formats_us_dollars_in_english()
    {
        $formatted = PriceFormatter::format(10.50, 'US', 'en');
        $this->assertEquals('$10.50', $formatted);
    }

    /** @test */
    public function it_formats_us_dollars_in_arabic()
    {
        $formatted = PriceFormatter::format(10.50, 'US', 'ar');
        $this->assertEquals('10.50 دولار', $formatted);
    }

    /** @test */
    public function it_formats_with_default_when_country_not_found()
    {
        $formatted = PriceFormatter::format(15, 'XX', 'en');
        $this->assertEquals('$15.00', $formatted);
    }

    /** @test */
    public function it_formats_with_english_when_language_not_found()
    {
        $formatted = PriceFormatter::format(20, 'EG', 'fr');
        $this->assertEquals('20 LE', $formatted);
    }

    /** @test */
    public function it_gets_currency_code()
    {
        $code = PriceFormatter::getCurrencyCode('EG');
        $this->assertEquals('EGP', $code);
    }

    /** @test */
    public function it_gets_currency_symbol()
    {
        $symbol = PriceFormatter::getCurrencySymbol('EG', 'en');
        $this->assertEquals('LE', $symbol);
    }
}
