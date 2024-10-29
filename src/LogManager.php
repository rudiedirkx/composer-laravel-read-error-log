<?php

namespace rdx\readerrorlog;

class LogManager {

	public function __construct(
		protected string $cwd,
	) {}

	public function findProjectPath(string $logfile) : ?string {
		if ($logfile[0] != '/' && file_exists("$this->cwd/composer.json")) {
			return "$this->cwd/";
		}

		return null;
	}

	public function getReader(string $logfile, bool $forceSize = false) : LogReader {
		if ($logfile[0] == '/') {
			return new LogReader($logfile, $forceSize);
		}

		return new LogReader("$this->cwd/$logfile", $forceSize);
	}

}
