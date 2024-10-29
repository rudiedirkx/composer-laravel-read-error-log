<?php

namespace rdx\readerrorlog;

use Generator;
use IteratorAggregate;

class LogReader /*implements IteratorAggregate*/ {

	const MAX_SIZES = [
		1000, // Unzipped, 1000 MB
		100, // Zipped, 100 MB
	];

	const TZ_REGEX = '[\w\/]+';
	const DATE_REGEX = '\[(\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d|\d\d\-\w+\-\d\d\d\d \d\d:\d\d:\d\d [\w\/]+)\]';

	protected /*resource*/ $fh;

	public function __construct(
		protected string $logfile,
		bool $forceSize,
	) {
		$zipped = str_ends_with($logfile, '.gz');

		if (!$forceSize && file_exists($logfile) && ($bytes = filesize($logfile)) > static::getMaxSize($zipped)) {
			$mb = round($bytes / 1e6);
			throw new LogReaderException(sprintf("File is too big (%d MB). Use --force-size if you're sure.", $mb));
		}

		$opener = $zipped ? 'gzopen' : 'fopen';
		if (!($this->fh = @$opener($logfile, 'r'))) {
			throw new LogReaderException(sprintf("Can't read log file '%s'", $logfile));
		}
	}

	public function getFirstLines(?string $projectPath = null) : Generator {
		$dateRegex = '#^' . static::DATE_REGEX . '#';
		$tzRegex = '# ' . static::TZ_REGEX . '$#';
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

	static protected function getMaxSize(bool $zipped) : int {
		$mb = static::MAX_SIZES[intval($zipped)];
		return 1e6 * $mb;
	}

}
