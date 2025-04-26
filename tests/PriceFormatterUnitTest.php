<?php

namespace YourName\PriceFormatter\Tests;

use Orchestra\Testbench\TestCase;
use YourName\PriceFormatter\PriceFormatter;

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
                return require __DIR__ . '/../config/price-formatter.php';
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
}
