<?php

namespace rdx\readerrorlog;

class LogLine {

	public function __construct(
		protected string $line,
		protected int $utc,
	) {}

	public function getLine() : string {
		return $this->line;
	}

	public function getError() : string {
		return preg_replace('# \{".+#', '', $this->line);
	}

	public function getUtc() : int {
		return $this->utc;
	}

	public function getDatetime() : string {
		return date('Y-m-d H:i:s', $this->utc);
	}

}
