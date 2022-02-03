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

use Laminas\Code\Reflection\DocBlock\Tag\ReturnTag;
use Laminas\Code\Reflection\DocBlockReflection;
use Laminas\Code\Reflection\FunctionReflection as BaseFunctionReflection;
use Laminas\Code\Reflection\Exception;

class FunctionReflection extends BaseFunctionReflection
{
    /**
     * Get function DocBlock
     *
     * @throws Exception\InvalidArgumentException
     * @return DocBlockReflection|false
     */
    public function getDocBlock()
    {
        if ('' == $this->getDocComment()) {
            return false;
        }

        return new DocBlockReflection($this);
    }

    public function getParameters()
    {
        $phpReflections  = parent::getParameters();
        $zendReflections = [];
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance          = new ParameterReflection($this->getName(), $phpReflection->getName());
            $zendReflections[] = $instance;
            unset($phpReflection);
        }
        unset($phpReflections);

        return $zendReflections;
    }

    /**
     * Get method prototype
     *
     * @param string $format
     * @return array|string
     *
     * @psalm-template TFormat of string
     * @psalm-param TFormat $format
     * @psalm-return (TFormat is self::PROTOTYPE_AS_STRING ? string : array{
     *     namespace: string,
     *     name: string,
     *     return: string,
     *     arguments: array<string, array{
     *         type: ?string,
     *         required: bool,
     *         by_ref: bool,
     *         default: mixed,
     *     }>,
     * })
     */
    public function getPrototype($format = self::PROTOTYPE_AS_ARRAY)
    {
        $docBlock    = $this->getDocBlock();
        if ($docBlock !== false) {
            $return      = $docBlock->getTag('return');
            assert($return instanceof ReturnTag);
            $returnTypes = $return->getTypes();
            assert(!empty($returnTypes));
            $returnType  = count($returnTypes) > 1 ? implode('|', $returnTypes) : $returnTypes[0];
        } else {
            $returnType = 'mixed';
        }

        $prototype = [
            'namespace' => $this->getNamespaceName(),
            'name'      => substr($this->getName(), strlen($this->getNamespaceName()) + 1),
            'return'    => $returnType,
            'arguments' => [],
        ];

        $parameters = $this->getParameters();
        foreach ($parameters as $parameter) {
            $prototype['arguments'][$parameter->getName()] = [
                'type'     => $parameter->detectType(),
                'required' => ! $parameter->isOptional(),
                'by_ref'   => $parameter->isPassedByReference(),
                'default'  => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            ];
        }

        if ($format == self::PROTOTYPE_AS_STRING) {
            $line = $prototype['return'] . ' ' . $prototype['name'] . '(';
            $args = [];
            foreach ($prototype['arguments'] as $name => $argument) {
                $argsLine = ($argument['type']
                    ? $argument['type'] . ' '
                    : '') . ($argument['by_ref'] ? '&' : '') . '$' . $name;
                if (! $argument['required']) {
                    $argsLine .= ' = ' . var_export($argument['default'], true);
                }
                $args[] = $argsLine;
            }
            $line .= implode(', ', $args);
            $line .= ')';

            return $line;
        }

        return $prototype;
    }
}
