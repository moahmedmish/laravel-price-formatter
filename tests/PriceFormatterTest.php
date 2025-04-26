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

    /** @test */
    public function it_can_format_currency_from_built_in_currencies()
    {
        // This test assumes JPY is not in the default config but is in the built-in currencies
        $this->app['config']->set('price-formatter.currencies.JP', null);
        
        $formatted = PriceFormatter::format(1000, 'JP', 'en');
        $this->assertEquals('¥1,000', $formatted);
    }

    /** @test */
    public function it_can_get_currency_symbol_from_built_in_currencies()
    {
        // This test assumes JPY is not in the default config but is in the built-in currencies
        $this->app['config']->set('price-formatter.currencies.JP', null);
        
        $symbol = PriceFormatter::getCurrencySymbol('JP', 'en');
        $this->assertEquals('¥', $symbol);
    }

    /** @test */
    public function it_can_load_custom_currencies()
    {
        // Create a temporary custom currencies file
        $tempFile = sys_get_temp_dir() . '/custom_currencies.json';
        file_put_contents($tempFile, json_encode([
            'currencies' => [
                'XYZ' => [
                    'name' => 'Test Currency',
                    'country' => 'TEST',
                    'symbol' => [
                        'en' => 'T$',
                        'native' => 'T$'
                    ]
                ]
            ]
        ]));
        
        $this->app['config']->set('price-formatter.custom_currencies_path', $tempFile);
        
        $symbol = PriceFormatter::getCurrencySymbol('TEST', 'en');
        $this->assertEquals('T$', $symbol);
        
        // Clean up
        @unlink($tempFile);
    }

    /** @test */
    public function custom_currencies_override_built_in_ones()
    {
        // Create a temporary custom currencies file that overrides USD
        $tempFile = sys_get_temp_dir() . '/custom_currencies.json';
        file_put_contents($tempFile, json_encode([
            'currencies' => [
                'USD' => [
                    'name' => 'Custom Dollar',
                    'country' => 'UNITED STATES',
                    'symbol' => [
                        'en' => 'USD$',
                        'native' => 'USD$'
                    ]
                ]
            ]
        ]));
        
        $this->app['config']->set('price-formatter.custom_currencies_path', $tempFile);
        
        $symbol = PriceFormatter::getCurrencySymbol('US', 'en');
        $this->assertEquals('USD$', $symbol);
        
        // Clean up
        @unlink($tempFile);
    }
}
