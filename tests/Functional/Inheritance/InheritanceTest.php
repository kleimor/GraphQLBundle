<?php

namespace Overblog\GraphQLBundle\Tests\Functional\Inheritance;

use Overblog\GraphQLBundle\Config\Processor\InheritanceProcessor;
use Overblog\GraphQLBundle\Tests\Functional\TestCase;

class InheritanceTest extends TestCase
{
    /** @var array */
    private $config;

    protected function setUp()
    {
        parent::setUp();

        static::bootKernel(['test_case' => 'inheritance']);
        $this->config = (array) static::$kernel->getContainer()->getParameter('overblog_graphql_types.config');
    }

    public function testObjectInheritance()
    {
        $this->assertArrayHasKey('Query', $this->config);
        // TODO(mcg-web): understand why travis fields order diffed from local test
        $this->assertEquals(
            [
                'type' => 'object',
                InheritanceProcessor::INHERITS_KEY => ['QueryFoo', 'QueryBar', 'QueryHelloWord'],
                'class_name' => 'QueryType',
                'decorator' => false,
                'config' => [
                    'fields' => [
                        'sayHello' => [
                            'type' => 'String',
                            'args' => [],
                        ],
                        'period' => [
                            'type' => 'Period',
                            'args' => [],
                        ],
                        'bar' => [
                            'type' => 'String',
                            'args' => [],
                        ],
                    ],
                    'name' => 'Query',
                    'interfaces' => ['QueryHelloWord'],
                ],
            ],
            $this->config['Query']
        );
    }

    public function testEnumInheritance()
    {
        $this->assertArrayHasKey('Period', $this->config);
        $this->assertSame(
            [
                'type' => 'enum',
                InheritanceProcessor::INHERITS_KEY => ['Day', 'Month', 'Year'],
                'class_name' => 'PeriodType',
                'decorator' => false,
                'config' => [
                    'values' => [
                        'YEAR' => ['value' => 3],
                        'MONTH' => ['value' => 2],
                        'DAY' => ['value' => 1],
                    ],
                    'name' => 'Period',
                ],
            ],
            $this->config['Period']
        );
    }

    public function testDecoratorTypeShouldRemovedFromFinalConfig()
    {
        $this->assertArrayNotHasKey('QueryBarDecorator', $this->config);
        $this->assertArrayNotHasKey('QueryFooDecorator', $this->config);
    }

    public function testDecoratorInterfacesShouldMerge()
    {
        $this->assertArrayHasKey('AandB', $this->config);
        $this->assertSame(
            [
                'type' => 'object',
                InheritanceProcessor::INHERITS_KEY => ['DecoratorA'],
                'class_name' => 'AandBType',
                'decorator' => false,
                'config' => [
                    'interfaces' => ['InterfaceA', 'InterfaceB'],
                    'fields' => [
                        'a' => [
                            'type' => 'String',
                            'args' => [],
                        ],
                        'b' => [
                            'type' => 'String',
                            'args' => [],
                        ],
                    ],
                    'name' => 'AandB',
                ],
            ],
            $this->config['AandB']
        );
    }
}
