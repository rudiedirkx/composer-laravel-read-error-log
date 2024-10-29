<?php

namespace rdx\readerrorlog\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use rdx\readerrorlog\ErrorCount;
use rdx\readerrorlog\LogReaderException;

class CountCommand extends Command {

	protected function configure() {
		$this->setName('count');
		$this->setDescription('Count unique errors.');
		$this->setDefinition([
			new InputArgument('file', InputArgument::REQUIRED, 'The log file to read'),
			new InputOption('project-path', null, InputOption::VALUE_REQUIRED, 'Remove this project path from messages.'),
			new InputOption('after', null, InputOption::VALUE_REQUIRED, 'Only count errors after this datetime.'),
			new InputOption('sort', null, InputOption::VALUE_REQUIRED, 'Sort by num|date'),
			new InputOption('time', null, InputOption::VALUE_NONE, 'Show full time with date'),
			new InputOption('force-size', null, InputOption::VALUE_NONE, 'Read the full log, no matter the size'),
		]);
	}

	protected function execute(InputInterface $input, OutputInterface $output) : int {
		$manager = $this->getApplication()->getManager();

		$logfile = $input->getArgument('file');
		$projectPath = $input->getOption('project-path') ?: $manager->findProjectPath($logfile);

		$forceSize = $input->getOption('force-size');

		$time = microtime(true);

		try {
			$reader = $manager->getReader($logfile, $forceSize);
		}
		catch (LogReaderException $ex) {
			throw new RuntimeException($ex->getMessage());
		}

		$after = $input->getOption('after');
		$after = $after ? strtotime($after) : 0;

		$errors = [];
		foreach ($reader->getFirstLines($projectPath) as $line) {
			if ($line->getUtc() > $after) {
				$error = $line->getError();
				$errors[$error] ??= new ErrorCount();
				$errors[$error]->countInstance($line);
			}
		}

		$sort = $input->getOption('sort');
		if ($sort === 'date') {
			$sorter = fn($a, $b) => $a->getUtc() <=> $b->getUtc();
		}
		else {
			$sorter = fn($a, $b) => $a->getNum() <=> $b->getNum();
		}
		uasort($errors, $sorter);

		$fullTime = $input->getOption('time');
		$dtFormat = $fullTime ? 'Y-m-d H:i:s' : 'Y-m-d';

		$numPad = 1;
		foreach ($errors as $count) {
			$numPad = max($numPad, strlen(strval($count->getNum())));
		}

		$time = microtime(true) - $time;

		foreach ($errors as $error => $count) {
			$datetime = date($dtFormat, $count->getUtc());
			printf("% {$numPad}d  (%s)  %s\n", $count->getNum(), $datetime, $error);
		}
		printf("\n%d different errors. %.1f sec.\n", count($errors), $time);

		return 0;
	}

}
