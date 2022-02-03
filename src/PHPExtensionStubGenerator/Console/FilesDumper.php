<?php
declare(strict_types=1);

namespace PHPExtensionStubGenerator\Console;

use Iterator;
use ReflectionExtension;
use Zend\Console\Adapter\AdapterInterface;
use PHPExtensionStubGenerator\FilesDumper as BaseFilesDumper;
use Symfony\Component\Console\Output\OutputInterface;

class FilesDumper extends BaseFilesDumper
{
    /** @var OutputInterface */
    private $console;

    public function __construct(ReflectionExtension $reflectionExtension, OutputInterface $output)
    {
        parent::__construct($reflectionExtension);
        $this->output = $output;
    }

    protected function getGenerationTargets() : Iterator
    {
        foreach (parent::getGenerationTargets() as $file => $code) {
            $this->output->writeLine($file);
            yield $file => $code;
        }
    }
}
