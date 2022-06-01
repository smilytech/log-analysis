<?php

namespace App\Command;


use App\Service\LogService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

#[AsCommand(name: 'log:process')]
class LogParserCommand extends Command
{
    protected static $defaultName = 'log:process';

    protected static $defaultDescription = 'Parse and Process Log File to DB';

    private $logParser;

    public function __construct(LogService $logParser)
    {
        $this->logParser = $logParser;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'The Location of the Log File to parse and process of the user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument("file");

        if (!$file) {
            $output->writeln("File Argument not provided.");
            return Command::INVALID;
        }

        try {
            $this->logParser->process($file);
            return Command::SUCCESS;
        } catch (FileNotFoundException $ex) {
            $output->writeln("Invalid File specified: {$ex->getMessage()}");
            return Command::INVALID;
        } catch (\Exception $ex) {
            $output->writeln("Error Occured: {$ex->getMessage()}");
            return Command::FAILURE;
        }
    }

}
