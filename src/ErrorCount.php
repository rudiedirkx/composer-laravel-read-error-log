<?php

namespace rdx\readerrorlog;

class ErrorCount {

	public function __construct(
		protected int $utc = 0,
		protected int $num = 0,
	) {}

	public function countInstance(LogLine $line) : void {
		$this->utc = $line->getUtc();
		$this->num++;
	}

	public function getUtc() : int {
		return $this->utc;
	}

	public function getNum() : int {
		return $this->num;
	}

}
