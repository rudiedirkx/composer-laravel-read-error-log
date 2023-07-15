<?php

namespace rdx\readerrorlog;

use Generator;
use IteratorAggregate;

class LogReader /*implements IteratorAggregate*/ {

	const TZ_REGEX = '[\w\/]+';
	const DATE_REGEX = '\[(\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d|\d\d\-\w+\-\d\d\d\d \d\d:\d\d:\d\d [\w\/]+)\]';

	protected /*resource*/ $fh;

	public function __construct(
		protected string $logfile,
	) {
		if (!($this->fh = @fopen($logfile, 'r'))) {
			throw new LogReaderException(sprintf("Can't read log file '%s'", $logfile));
		}
	}

	public function getFirstLines(?string $projectPath = null) : Generator {
		$dateRegex = '#^' . self::DATE_REGEX . '#';
		$tzRegex = '# ' . self::TZ_REGEX . '$#';
		while (($buffer = fgets($this->fh)) !== false) {
			if (preg_match($dateRegex, trim($buffer), $match)) {
				$utc = strtotime(preg_replace($tzRegex, '', trim($match[1])));
				$error = trim(substr($buffer, strlen($match[0])));
				if ($projectPath) {
					$error = str_replace($projectPath, '', $error);
				}
				yield new LogLine($error, $utc);
			}
		}
	}

}
