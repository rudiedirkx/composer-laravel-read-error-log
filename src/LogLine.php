<?php

namespace rdx\readerrorlog;

class LogLine {

	public function __construct(
		protected string $line,
		protected int $utc,
	) {
		$this->trimLine();
	}

	protected function trimLine() : void {
		// Remove Laravel's "local.ERROR: "
		$this->line = preg_replace('#[a-z]+\.[A-Z]+: #', '', $this->line);
	}

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
