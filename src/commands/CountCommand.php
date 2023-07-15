<?php

namespace rdx\readerrorlog\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use rdx\readerrorlog\LogReaderException;

class CountCommand extends Command {

	protected function configure() {
		$this->setName('count');
		$this->setDescription('Count unique errors.');
		$this->setDefinition([
			new InputArgument('file', InputArgument::REQUIRED, 'The log file to read'),
			new InputOption('project-path', null, InputOption::VALUE_REQUIRED, 'Remove this project path from messages.'),
			new InputOption('after', null, InputOption::VALUE_REQUIRED, 'Only count errors after this datetime.'),
		]);
	}

	protected function execute(InputInterface $input, OutputInterface $output) : int {
		$manager = $this->getApplication()->getManager();
// print_r($manager);

		$logfile = $input->getArgument('file');
		$projectPath = $input->getOption('project-path') ?: $manager->findProjectPath($logfile);
// var_dump($projectPath);

		try {
			$reader = $manager->getReader($logfile);
		}
		catch (LogReaderException $ex) {
			// $this->getApplication()->renderThrowable($ex, $output);
			$output->writeLn('<error>Invalid file.</error>');
			return 1;
		}

		$after = $input->getOption('after');
		$after = $after ? strtotime($after) : 0;

		$counts = $utcs = [];
		foreach ($reader->getFirstLines($projectPath) as $line) {
			if ($line->getUtc() > $after) {
				$error = $line->getError();
				$counts[$error] ??= 0;
				$counts[$error]++;

				$utcs[$error] = $line->getUtc();
			}
		}
		asort($counts, SORT_NUMERIC);

		foreach ($counts as $error => $num) {
			echo sprintf("% 4d - %s (last @ %s)\n", $num, $error, date('Y-m-d H:i:s', $utcs[$error]));
		}
		echo "\n";
		echo count($counts) . " different errors\n";

		return 0;
	}

}
