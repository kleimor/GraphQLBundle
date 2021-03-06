<?php

namespace Overblog\GraphQLBundle\Tests\Request;

use Overblog\GraphQLBundle\Request\UploadParserTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UploadParserTraitTest extends TestCase
{
    use UploadParserTrait;

    /**
     * @param string $location
     * @param string $expected
     *
     * @dataProvider locationsProvider
     */
    public function testLocationToPropertyAccessPath($location, $expected)
    {
        $actual = $this->locationToPropertyAccessPath($location);
        $this->assertSame($expected, $actual);
    }

    /**
     * @param array  $operations
     * @param array  $map
     * @param array  $files
     * @param array  $expected
     * @param string $message
     *
     * @dataProvider payloadProvider
     */
    public function testHandleUploadedFiles(array $operations, array $map, array $files, array $expected, $message)
    {
        $actual = $this->handleUploadedFiles(['operations' => \json_encode($operations), 'map' => \json_encode($map)], $files);
        $this->assertSame($expected, $actual, $message);
    }

    public function testBindUploadedFilesFileNotFound()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('File 0 is missing in the request.');
        $operations = ['query' => '', 'variables' => ['file' => null]];
        $map = ['0' => ['variables.file']];
        $files = [];
        $this->bindUploadedFiles($operations, $map, $files);
    }

    public function testBindUploadedFilesOperationPathNotFound()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Map entry "variables.file" could not be localized in operations.');
        $operations = ['query' => '', 'variables' => []];
        $map = ['0' => ['variables.file']];
        $files = ['0' => new \stdClass()];
        $this->bindUploadedFiles($operations, $map, $files);
    }

    public function testIsUploadPayload()
    {
        $this->assertFalse($this->isUploadPayload([]));
        $this->assertFalse($this->isUploadPayload(['operations' => []]));
        $this->assertFalse($this->isUploadPayload(['map' => []]));
        $this->assertFalse($this->isUploadPayload(['operations' => null, 'map' => []]));
        $this->assertFalse($this->isUploadPayload(['operations' => [], 'map' => null]));
        $this->assertFalse($this->isUploadPayload(['operations' => null, 'map' => null]));
        $this->assertFalse($this->isUploadPayload(['map' => [], 'operations' => []]), '"operations" must be place before "map".');
        $this->assertTrue($this->isUploadPayload(['operations' => [], 'map' => []]));
    }

    public function payloadProvider()
    {
        $files = ['0' => new \stdClass()];
        yield [
            ['query' => 'mutation($file: Upload!) { singleUpload(file: $file) { id } }', 'variables' => ['file' => null]],
            ['0' => ['variables.file']],
            $files,
            ['query' => 'mutation($file: Upload!) { singleUpload(file: $file) { id } }', 'variables' => ['file' => $files['0']]],
            'single file',
        ];
        $files = ['0' => new \stdClass(), 1 => new \stdClass()];
        yield [
            ['query' => 'mutation($files: [Upload!]!) { multipleUpload(files: $files) { id } }', 'variables' => ['files' => [null, null]]],
            ['0' => ['variables.files.0'], '1' => ['variables.files.1']],
            $files,
            ['query' => 'mutation($files: [Upload!]!) { multipleUpload(files: $files) { id } }', 'variables' => ['files' => [$files['0'], $files[1]]]],
            'file list',
        ];
        $files = [0 => new \stdClass(), '1' => new \stdClass(), '2' => new \stdClass()];
        yield [
            [
                ['query' => 'mutation($file: Upload!) { singleUpload(file: $file) { id } }', 'variables' => ['file' => null]],
                ['query' => 'mutation($files: [Upload!]!) { multipleUpload(files: $files) { id } }', 'variables' => ['files' => [null, null]]],
            ],
            ['0' => ['0.variables.file'], '1' => ['1.variables.files.0'], '2' => ['1.variables.files.1']],
            $files,
            [
                ['query' => 'mutation($file: Upload!) { singleUpload(file: $file) { id } }', 'variables' => ['file' => $files[0]]],
                ['query' => 'mutation($files: [Upload!]!) { multipleUpload(files: $files) { id } }', 'variables' => ['files' => [$files['1'], $files['2']]]],
            ],
            'batching',
        ];
    }

    public function locationsProvider()
    {
        yield ['variables.file', '[variables][file]'];
        yield ['variables.files.0', '[variables][files][0]'];
        yield ['variables.files.1', '[variables][files][1]'];
        yield ['0.variables.file', '[0][variables][file]'];
        yield ['1.variables.files.0', '[1][variables][files][0]'];
        yield ['1.variables.files.1', '[1][variables][files][1]'];
    }
}
