<?php

namespace MoahmedMish\PriceFormatter\Tests;

use Orchestra\Testbench\TestCase;
use MoahmedMish\PriceFormatter\PriceFormatterServiceProvider;
use MoahmedMish\PriceFormatter\Facades\PriceFormatter;
use MoahmedMish\PriceFormatter\Exceptions\InvalidRoundingModeException;

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
        $this->assertEquals('٥ ج م', $formatted);
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
        $this->assertEquals('١٠٫٥٠ دولار', $formatted);
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

    /** @test */
    public function it_uses_eastern_arabic_numerals_for_arabic_language()
    {
        $formatted = PriceFormatter::format(1234.56, 'EG', 'ar');
        $this->assertEquals('١٢٣٤٫٥٦ ج م', $formatted);
    }

    /** @test */
    public function it_uses_western_arabic_numerals_for_english_language()
    {
        $formatted = PriceFormatter::format(1234.56, 'EG', 'en');
        $this->assertEquals('1,234.56 LE', $formatted);
    }

    /** @test */
    public function it_can_force_eastern_arabic_numerals_for_all_languages()
    {
        $this->app['config']->set('price-formatter.numerals.force_eastern_arabic', true);
        
        $formatted = PriceFormatter::format(1234.56, 'US', 'en');
        $this->assertEquals('$١٢٣٤٫٥٦', $formatted);
    }

    /** @test */
    public function it_can_force_western_arabic_numerals_for_arabic_language()
    {
        $this->app['config']->set('price-formatter.numerals.force_western_arabic', true);
        
        $formatted = PriceFormatter::format(1234.56, 'EG', 'ar');
        $this->assertEquals('1,234.56 ج م', $formatted);
    }

    /** @test */
    public function it_respects_language_specific_numeral_settings()
    {
        // Override the default behavior for Arabic
        $this->app['config']->set('price-formatter.currencies.EG.formats.ar.use_eastern_arabic_numerals', false);
        
        $formatted = PriceFormatter::format(1234.56, 'EG', 'ar');
        $this->assertEquals('1,234.56 ج م', $formatted);
    }

    /** @test */
    public function it_converts_decimal_and_thousand_separators_correctly()
    {
        $formatted = PriceFormatter::format(1234567.89, 'EG', 'ar');
        $this->assertEquals('١٬٢٣٤٬٥٦٧٫٨٩ ج م', $formatted);
    }

    /** @test */
    public function it_formats_using_app_locale()
    {
        $this->app['config']->set('app.locale', 'ar');
        $this->app['config']->set('price-formatter.locale.use_app_locale', true);
        $this->app['config']->set('price-formatter.locale.locale_to_country_map', ['ar' => 'EG']);
        
        $formatted = PriceFormatter::formatLocalized(5);
        $this->assertEquals('٥ ج م', $formatted);
    }

    /** @test */
    public function it_formats_with_accounting_format()
    {
        $formatted = PriceFormatter::formatAccounting(-10.50, 'US', 'en');
        $this->assertEquals('($10.50)', $formatted);
    }

    /** @test */
    public function it_formats_with_compact_notation()
    {
        $formatted = PriceFormatter::formatCompact(1500, 'US', 'en');
        $this->assertEquals('$1.5K', $formatted);
        
        $formatted = PriceFormatter::formatCompact(1500000, 'US', 'en');
        $this->assertEquals('$1.5M', $formatted);
        
        $formatted = PriceFormatter::formatCompact(1500000000, 'US', 'en');
        $this->assertEquals('$1.5B', $formatted);
    }

    /** @test */
    public function it_formats_percentages()
    {
        $formatted = PriceFormatter::formatPercentage(0.255, 1);
        $this->assertEquals('25.5%', $formatted);
    }

    /** @test */
    public function it_applies_different_rounding_modes()
    {
        // Test ceil rounding
        $this->app['config']->set('price-formatter.default.rounding_mode', 'ceil');
        $formatted = PriceFormatter::format(10.001, 'US', 'en');
        $this->assertEquals('$10.01', $formatted);
        
        // Test floor rounding
        $this->app['config']->set('price-formatter.default.rounding_mode', 'floor');
        $formatted = PriceFormatter::format(10.999, 'US', 'en');
        $this->assertEquals('$10.99', $formatted);
        
        // Test half_up rounding
        $this->app['config']->set('price-formatter.default.rounding_mode', 'half_up');
        $formatted = PriceFormatter::format(10.505, 'US', 'en');
        $this->assertEquals('$10.51', $formatted);
        
        // Test half_down rounding
        $this->app['config']->set('price-formatter.default.rounding_mode', 'half_down');
        $formatted = PriceFormatter::format(10.505, 'US', 'en');
        $this->assertEquals('$10.50', $formatted);
    }

    /** @test */
    public function it_throws_exception_for_invalid_rounding_mode()
    {
        $this->expectException(InvalidRoundingModeException::class);
        
        $this->app['config']->set('price-formatter.default.rounding_mode', 'invalid_mode');
        PriceFormatter::format(10.50, 'US', 'en');
    }

    /** @test */
    public function it_can_format_cryptocurrency()
    {
        $this->app['config']->set('price-formatter.currencies.BTC', [
            'code' => 'BTC',
            'formats' => [
                'en' => [
                    'symbol' => '₿',
                    'position' => 'before',
                    'separator' => '',
                    'decimals' => 8,
                ],
            ],
        ]);
        
        $formatted = PriceFormatter::format(0.00012345, 'BTC', 'en');
        $this->assertEquals('₿0.00012345', $formatted);
    }
}
