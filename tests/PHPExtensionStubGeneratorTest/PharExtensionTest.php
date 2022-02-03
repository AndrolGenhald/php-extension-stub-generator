<?php
namespace PHPReRewindTest;

use ReflectionExtension;
use PHPUnit\Framework\TestCase;
use PHPExtensionStubGenerator\GeneratorDumper;

class PharExtensionTest extends TestCase
{
    /**
     * @var string
     */
    private $output;

    public function setUp(): void
    {
        $generator = new GeneratorDumper(new ReflectionExtension('phar'));
        $this->output = implode("\n", iterator_to_array($generator->getGenerates()));
    }

    /**
     * @dataProvider outputContainsProvider
     */
    public function testOutputContains(string $string): void
    {
        $this->assertStringContainsString($string, $this->output);
    }

    /**
     * @return iterable<array{string: string}>
     */
    public function outputContainsProvider()
    {
        yield "class" => ["string" => "class PharException extends Exception"];
        yield "classConst" => ["string" => "public const CURRENT_MODE_MASK = 240;"];
        yield "method" => ["string" => 'public function __construct(string $filename, int $flags = 12288, ?string $alias = null)'];
    }
}
