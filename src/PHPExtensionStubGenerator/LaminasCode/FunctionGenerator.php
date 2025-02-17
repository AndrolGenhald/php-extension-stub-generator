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

class FunctionGenerator
{
    /**
     * @param array{
     *     namespace: string,
     *     name: string,
     *     return: string,
     *     arguments: array<string, array{
     *         type: ?string,
     *         required: bool,
     *         by_ref: bool,
     *         default: mixed,
     *     }>,
     * } $prototype
     * @return non-empty-string
     */
    public static function generateByPrototypeArray(array $prototype): string
    {
        $line = 'function' . ' ' . $prototype['name'] . '(';
        $args = [];
        foreach ($prototype['arguments'] as $name => $argument) {
            $type = ($argument['type'] && $argument['type'] !== 'resource') ? "{$argument['type']} " : "";
            $argsLine = $type . ($argument['by_ref'] ? '&' : '') . '$' . $name;
            if (!$argument['required']) {
                $argsLine .= ' = ' . var_export($argument['default'], true);
            }
            $args[] = $argsLine;
        }
        $line .= implode(', ', $args);
        $line .= ')' . ($prototype['return'] !== 'mixed' ? ": {$prototype['return']}" : "") . ' {}';

        return $line;
    }
}
