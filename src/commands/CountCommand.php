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
			new InputOption('project-path', null, InputOption::VALUE_REQUIRED, 'Optional project path to remove from messages.'),
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

		$errors = [];
		foreach ($reader->getFirstLines($projectPath) as $line) {
			$error = $line->getError();
			$errors[$error] ??= 0;
			$errors[$error]++;
		}
		asort($errors, SORT_NUMERIC);

		foreach ($errors as $error => $num) {
			echo sprintf("% 4d - %s\n", $num, $error);
		}
		echo "\n";
		echo count($errors) . " different errors\n";

		return 0;
	}

}
