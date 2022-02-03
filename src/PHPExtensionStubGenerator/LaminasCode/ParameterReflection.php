<?php
declare(strict_types=1);

/**
 * most of parts is borrowed from zendframework/zend-code
 * https://github.com/zendframework/zend-code
 *
 * This source is aimed for hack to override zend-code.
 *
 * @license New BSD, code from Zend Framework
 * https://github.com/zendframework/zend-code/blob/master/LICENSE.md
 */

namespace PHPExtensionStubGenerator\LaminasCode;

use Laminas\Code\Reflection\DocBlock\Tag\ParamTag;
use Laminas\Code\Reflection\DocBlockReflection;
use Laminas\Code\Reflection\MethodReflection;
use Laminas\Code\Reflection\ParameterReflection as BaseParameterReflection;
use ReflectionClass;
use ReflectionMethod;
use ReturnTypeWillChange;

class ParameterReflection extends BaseParameterReflection
{
    /**
     * Get declaring function reflection object
     *
     * @return FunctionReflection|MethodReflection
     */
    #[ReturnTypeWillChange]
    public function getDeclaringFunction()
    {
        $phpReflection = parent::getDeclaringFunction();
        if ($phpReflection instanceof ReflectionMethod) {
            $laminasReflection = new MethodReflection($this->getDeclaringClass()->getName(), $phpReflection->getName());
        } else {
            $laminasReflection = new FunctionReflection($phpReflection->getName());
        }
        unset($phpReflection);

        return $laminasReflection;
    }

    /**
     * Get parameter type
     *
     * @return string|null
     */
    public function detectType(): ?string
    {
        if (
            method_exists($this, 'getType')
            && null !== ($type = $this->getType())
            && $type->isBuiltin()
        ) {
            return $type->getName();
        }

        if (null !== $type && $type->getName() === 'self') {
            return $this->getDeclaringClass()->getName();
        }

        if (($class = $this->getClass()) instanceof ReflectionClass) {
            return $class->getName();
        }

        $docBlock = $this->getDeclaringFunction()->getDocBlock();

        if (! $docBlock instanceof DocBlockReflection) {
            return null;
        }

        /** @var ParamTag[] $params */
        $params       = $docBlock->getTags('param');
        $paramTag     = $params[$this->getPosition()] ?? null;
        $variableName = '$' . $this->getName();

        if ($paramTag && ('' === $paramTag->getVariableName() || $variableName === $paramTag->getVariableName())) {
            return $paramTag->getTypes()[0] ?? '';
        }

        foreach ($params as $param) {
            if ($param->getVariableName() === $variableName) {
                return $param->getTypes()[0] ?? '';
            }
        }

        return null;
    }
}
