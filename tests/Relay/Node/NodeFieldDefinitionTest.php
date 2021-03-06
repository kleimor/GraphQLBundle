<?php

namespace Overblog\GraphQLBundle\Tests\Relay\Node;

use Overblog\GraphQLBundle\Relay\Node\NodeFieldDefinition;
use PHPUnit\Framework\TestCase;

class NodeFieldDefinitionTest extends TestCase
{
    /** @var NodeFieldDefinition */
    private $definition;

    public function setUp()
    {
        $this->definition = new NodeFieldDefinition();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Node "idFetcher" config is invalid.
     */
    public function testUndefinedIdFetcherConfig()
    {
        $this->definition->toMappingDefinition([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Node "idFetcher" config is invalid.
     */
    public function testIdFetcherConfigSetButIsNotString()
    {
        $this->definition->toMappingDefinition(['idFetcher' => 45]);
    }

    /**
     * @dataProvider validConfigProvider
     *
     * @param $idFetcher
     * @param $idFetcherCallbackArg
     * @param $nodeInterfaceType
     */
    public function testValidConfig($idFetcher, $idFetcherCallbackArg, $nodeInterfaceType = 'node')
    {
        $config = [
            'idFetcher' => $idFetcher,
            'inputType' => 'UserInput',
            'nodeInterfaceType' => $nodeInterfaceType,
        ];

        $expected = [
            'description' => 'Fetches an object given its ID',
            'type' => $nodeInterfaceType,
            'args' => ['id' => ['type' => 'ID!', 'description' => 'The ID of an object']],
            'resolve' => '@=resolver(\'relay_node_field\', [args, context, info, idFetcherCallback('.$idFetcherCallbackArg.')])',
        ];

        $this->assertSame($expected, $this->definition->toMappingDefinition($config));
    }

    public function validConfigProvider()
    {
        return [
            ['@=user.username', 'user.username'],
            ['toto', 'toto'],
            ['50', '50'],
            ['@=user.id', 'user.id', 'NodeInterface'],
        ];
    }
}
