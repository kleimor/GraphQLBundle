<?php

namespace Overblog\GraphQLBundle\Tests\Config\Parser;

use Overblog\GraphQLBundle\Config\Parser\GraphQL\ASTConverter\CustomScalarNode;
use Overblog\GraphQLBundle\Config\Parser\GraphQLParser;
use Overblog\GraphQLBundle\Tests\VersionHelper;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class GraphQLParserTest extends TestCase
{
    /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $containerBuilder;

    public function setUp()
    {
        $this->containerBuilder = $this->getMockBuilder(ContainerBuilder::class)->setMethods(['addResource'])->getMock();
    }

    public function testParse()
    {
        $fileName = \sprintf(
            __DIR__.\DIRECTORY_SEPARATOR.'fixtures'.\DIRECTORY_SEPARATOR.'graphql'.\DIRECTORY_SEPARATOR.'schema%s.graphql',
            VersionHelper::compareWebonyxGraphQLPHPVersion('0.12.0', '<') ? '-0.11' : ''
        );
        $expected = include __DIR__.'/fixtures/graphql/schema.php';

        $this->assertContainerAddFileToResources($fileName);
        $config = GraphQLParser::parse(new \SplFileInfo($fileName), $this->containerBuilder);
        $this->assertSame($expected, self::cleanConfig($config));
    }

    public function testParseEmptyFile()
    {
        $fileName = __DIR__.\DIRECTORY_SEPARATOR.'fixtures'.\DIRECTORY_SEPARATOR.'graphql'.\DIRECTORY_SEPARATOR.'empty.graphql';

        $this->assertContainerAddFileToResources($fileName);

        $config = GraphQLParser::parse(new \SplFileInfo($fileName), $this->containerBuilder);
        $this->assertSame([], $config);
    }

    public function testParseInvalidFile()
    {
        $fileName = __DIR__.'/fixtures/graphql/invalid.graphql';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('An error occurred while parsing the file "%s"', $fileName));
        GraphQLParser::parse(new \SplFileInfo($fileName), $this->containerBuilder);
    }

    public function testParseNotSupportedSchemaDefinition()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Schema definition is not supported right now.');
        GraphQLParser::parse(new \SplFileInfo(__DIR__.'/fixtures/graphql/not-supported-schema-definition.graphql'), $this->containerBuilder);
    }

    public function testCustomScalarTypeDefaultFieldValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Config entry must be override with ResolverMap to be used.');
        CustomScalarNode::mustOverrideConfig();
    }

    private function assertContainerAddFileToResources($fileName)
    {
        $this->containerBuilder->expects($this->once())
            ->method('addResource')
            ->with($fileName);
    }

    private static function cleanConfig($config)
    {
        foreach ($config as $key => &$value) {
            if (\is_array($value)) {
                $value = self::cleanConfig($value);
            }
        }

        return \array_filter($config, function ($item) {
            return !\is_array($item) || !empty($item);
        });
    }
}
