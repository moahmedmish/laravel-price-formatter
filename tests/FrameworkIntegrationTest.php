<?php

namespace MoahmedMish\PriceFormatter\Tests;

use MoahmedMish\PriceFormatter\Casts\MoneyCast;
use MoahmedMish\PriceFormatter\Rules\FormattedMoney;
use Orchestra\Testbench\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use MoahmedMish\PriceFormatter\PriceFormatterServiceProvider;
use MoahmedMish\PriceFormatter\Facades\PriceFormatter;

class FrameworkIntegrationTest extends TestCase
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
    public function it_registers_blade_directives()
    {
        $this->assertTrue(Blade::getCustomDirectives()['money'] ?? false);
        $this->assertTrue(Blade::getCustomDirectives()['moneyAccounting'] ?? false);
        $this->assertTrue(Blade::getCustomDirectives()['moneyCompact'] ?? false);
        $this->assertTrue(Blade::getCustomDirectives()['moneyLocalized'] ?? false);
    }

    /** @test */
    public function it_compiles_blade_directives()
    {
        $this->assertEquals(
            '<?php echo app(\'price-formatter\')->format(100, \'US\', \'en\'); ?>',
            Blade::compileString('@money(100, \'US\', \'en\')')
        );

        $this->assertEquals(
            '<?php echo app(\'price-formatter\')->formatAccounting(-100, \'US\', \'en\'); ?>',
            Blade::compileString('@moneyAccounting(-100, \'US\', \'en\')')
        );

        $this->assertEquals(
            '<?php echo app(\'price-formatter\')->formatCompact(1000, \'US\', \'en\'); ?>',
            Blade::compileString('@moneyCompact(1000, \'US\', \'en\')')
        );

        $this->assertEquals(
            '<?php echo app(\'price-formatter\')->formatLocalized(100, \'US\'); ?>',
            Blade::compileString('@moneyLocalized(100, \'US\')')
        );
    }

    /** @test */
    public function it_casts_money_attributes()
    {
        // Create a test model with money cast
        $model = new class extends Model {
            protected $casts = [
                'price' => MoneyCast::class.':US,en',
            ];
            
            // Mock the attribute retrieval
            public function getAttributeValue($key)
            {
                if ($key === 'price') {
                    return 100.50;
                }
                return parent::getAttributeValue($key);
            }
        };
        
        // Test that the price is formatted correctly
        $this->assertEquals('$100.50', $model->price);
    }

    /** @test */
    public function it_validates_money_values()
    {
        $rule = new FormattedMoney('USD');
        
        // Valid numeric values
        $this->assertTrue($rule->passes('price', 100));
        $this->assertTrue($rule->passes('price', 100.50));
        $this->assertTrue($rule->passes('price', '100'));
        $this->assertTrue($rule->passes('price', '100.50'));
        
        // Valid formatted values
        $this->assertTrue($rule->passes('price', '$100.50'));
        $this->assertTrue($rule->passes('price', '100.50 USD'));
        
        // Invalid values
        $this->assertFalse($rule->passes('price', 'not a number'));
        $this->assertFalse($rule->passes('price', 'USD'));
        $this->assertFalse($rule->passes('price', []));
        $this->assertFalse($rule->passes('price', null));
    }

    /** @test */
    public function it_provides_validation_error_message()
    {
        $rule = new FormattedMoney('USD');
        $this->assertEquals(
            'The :attribute must be a valid monetary value in USD format.',
            $rule->message()
        );
        
        $rule = new FormattedMoney();
        $this->assertEquals(
            'The :attribute must be a valid monetary value.',
            $rule->message()
        );
    }
}
