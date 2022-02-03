<?php
declare(strict_types=1);

namespace PHPExtensionStubGenerator;

use Generator;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Reflection\ClassReflection;
use ReflectionExtension;
use PHPExtensionStubGenerator\LaminasCode\ {
    FunctionGenerator, FunctionReflection
};

class GeneratorDumper
{
    /** @var ReflectionExtension */
    private $reflectionExtension;

    /** @var DocBlockGenerator|null */
    private $docBlockGenerator;

    public function __construct(ReflectionExtension $reflectionExtension)
    {
        $this->reflectionExtension = $reflectionExtension;
    }

    /**
     * @return Generator<int, string>
     */
    public function getGenerates() : Generator
    {
        yield from $this->generateConstants();
        yield from $this->generateFunctions();
        yield from $this->generateClasses();
    }

    public function setDocBlockGenerator(DocBlockGenerator $docBlockGenerator) : void
    {
        $this->docBlockGenerator = $docBlockGenerator;
    }

    public function getDocBlockGenerator() : DocBlockGenerator
    {
        if (!$this->docBlockGenerator instanceof DocBlockGenerator) {
            $docBlockGenerator = new DocBlockGenerator('auto generated file by PHPExtensionStubGenerator');
            $this->docBlockGenerator = $docBlockGenerator;
        }

        return $this->docBlockGenerator;
    }

    /**
     * @return Generator<int, string>
     */
    public function generateConstants() : Generator
    {
        $reflectionConstants = $this->reflectionExtension->getConstants();

        $declaredNamespaces = [];

        /** @var mixed $value */
        foreach ($reflectionConstants as $constant => $value) {
            $c = preg_split('#\\\#', $constant);

            // has namespace ?
            if (count($c) > 1) {
                list($namespaces, $constName) = array_chunk($c, count($c)-1);
                $constName = current($constName);

                $namespace = implode('\\', $namespaces);
                if (!isset($declaredNamespaces[$namespace])) {
                    $declaredNamespaces[$namespace] = true;
                    yield "namespace {$namespace};";
                }

                /** @var mixed */
                $encodeValue = is_string($value) ? sprintf('"%s"', $value) : $value;
                yield "const $constName = {$encodeValue};";
            } else {
                /** @var mixed */
                $encodeValue = is_string($value) ? sprintf('"%s"', $value) : $value;
                yield "const $constant = {$encodeValue};";
            }
        }
    }

    /**
     * @return Generator<int, string>
     */
    public function generateClasses() : Generator
    {
        /** @var \ReflectionClass $phpClassReflection */
        foreach ($this->reflectionExtension->getClasses() as $_fqcn => $phpClassReflection) {
            $classGenerator = ClassGenerator::fromReflection(new ClassReflection($phpClassReflection->getName()));

            yield $classGenerator->generate();
        }
    }

    /**
     * @return Generator<int, string>
     */
    public function generateFunctions() : Generator
    {
        $declaredNamespaces = [];
        foreach ($this->reflectionExtension->getFunctions() as $function_name => $_phpFunctionReflection) {

            $functionReflection = new FunctionReflection($function_name);

            $namespace = $functionReflection->getNamespaceName();
            if ($namespace && !isset($declaredNamespaces[$namespace])) {
                $declaredNamespaces[$namespace] = true;
                yield "namespace {$namespace};";
            }

            yield FunctionGenerator::generateByPrototypeArray($functionReflection->getPrototype());
        }
    }
}
