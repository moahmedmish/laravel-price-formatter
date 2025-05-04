<?php

namespace MoahmedMish\PriceFormatter\Tests;

use Orchestra\Testbench\TestCase;
use MoahmedMish\PriceFormatter\PriceFormatter;

class PriceFormatterUnitTest extends TestCase
{
    protected $priceFormatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->priceFormatter = new PriceFormatter();
        
        // Mock the config function
        $this->app->instance('config', new class {
            public function get($key, $default = null)
            {
                if ($key === 'price-formatter') {
                    return require __DIR__ . '/../config/price-formatter.php';
                }
                return $default;
            }
        });
    }

    /** @test */
    public function it_applies_correct_formatting_with_before_position()
    {
        $settings = [
            'symbol' => '$',
            'position' => 'before',
            'separator' => '',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimals' => 2
        ];
        
        $method = new \ReflectionMethod(PriceFormatter::class, 'applyFormatting');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->priceFormatter, 1234.56, $settings);
        $this->assertEquals('$1,234.56', $result);
    }

    /** @test */
    public function it_applies_correct_formatting_with_after_position()
    {
        $settings = [
            'symbol' => 'EUR',
            'position' => 'after',
            'separator' => ' ',
            'decimal_separator' => ',',
            'thousand_separator' => '.',
            'decimals' => 2
        ];
        
        $method = new \ReflectionMethod(PriceFormatter::class, 'applyFormatting');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->priceFormatter, 1234.56, $settings);
        $this->assertEquals('1.234,56 EUR', $result);
    }

    /** @test */
    public function it_handles_zero_decimals()
    {
        $settings = [
            'symbol' => '¥',
            'position' => 'before',
            'separator' => '',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'decimals' => 0
        ];
        
        $method = new \ReflectionMethod(PriceFormatter::class, 'applyFormatting');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->priceFormatter, 1234.56, $settings);
        $this->assertEquals('¥1,235', $result);
    }
    
    /** @test */
    public function it_loads_all_currencies()
    {
        $method = new \ReflectionMethod(PriceFormatter::class, 'loadAllCurrencies');
        $method->setAccessible(true);
        $method->invoke($this->priceFormatter);
        
        $property = new \ReflectionProperty(PriceFormatter::class, 'allCurrencies');
        $property->setAccessible(true);
        $currencies = $property->getValue($this->priceFormatter);
        
        $this->assertIsArray($currencies);
        $this->assertNotEmpty($currencies);
        $this->assertArrayHasKey('USD', $currencies);
        $this->assertArrayHasKey('EUR', $currencies);
        $this->assertArrayHasKey('JPY', $currencies);
    }
    
    /** @test */
    public function it_gets_currency_code_from_country()
    {
        $method = new \ReflectionMethod(PriceFormatter::class, 'getCurrencyCodeFromCountry');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->priceFormatter, 'UNITED STATES');
        $this->assertEquals('USD', $result);
    }
    
    /** @test */
    public function it_gets_formats_for_currency()
    {
        $method = new \ReflectionMethod(PriceFormatter::class, 'getFormatsForCurrency');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->priceFormatter, 'USD', 'en');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('en', $result);
        $this->assertEquals('$', $result['en']['symbol']);
        $this->assertEquals('before', $result['en']['position']);
    }
}
