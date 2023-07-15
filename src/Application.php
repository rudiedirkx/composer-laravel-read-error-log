<?php

namespace rdx\readerrorlog;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use rdx\readerrorlog\commands;

class Application extends BaseApplication {

	public function __construct(
		protected LogManager $manager,
	) {
		parent::__construct('read-error-log', '1.0');
	}

	public function getManager() : LogManager {
		return $this->manager;
	}

	protected function getDefaultCommands() : array {
		return [
			new commands\AllCommand(),
			new commands\CountCommand(),
		];
	}

	protected function getCommandName(InputInterface $input) : ?string {
		$name = parent::getCommandName($input);
		return $name ?: 'all';
	}

}
