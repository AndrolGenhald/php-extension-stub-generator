<?php

declare(strict_types=1);

namespace PHPExtensionStubGenerator\Console;

use PHPExtensionStubGenerator\GeneratorDumper;
use ReflectionExtension;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
    protected static $defaultName = "dump";

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument("extensionName", InputArgument::REQUIRED, "The extension to generate stubs for");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $errorOutput = $output;
        if ($output instanceof ConsoleOutputInterface) {
            $errorOutput = $output->getErrorOutput();
        }

        $extensionName = $input->getArgument("extensionName");
        if (!extension_loaded($extensionName)) {
            $errorOutput->writeln("<error>Extension $extensionName is not loaded</error>");
            return 1;
        }

        $extensionReflection = new ReflectionExtension($extensionName);
        $dumper = new GeneratorDumper($extensionReflection);
        $output->writeln("<?php");
        $output->writeln("// Generated with PHP version " . PHP_VERSION
            . " {$extensionReflection->getName()} version {$extensionReflection->getVersion()}\n"
        );
        foreach ($dumper->getGenerates() as $line) {
            $output->writeln($line);
        }

        return 0;
    }
}
