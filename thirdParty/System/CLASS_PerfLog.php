<?php

/*
 * Jer's yet undetermined license goes here
 */

const PERFLOGPATH = 'logs/PerfLog.txt';

class PerfLog {
	/** @var float */
	private $start;
	/** @var string */
	private $path;
	/** @var string */
	private $log;

	/**
	 * PerfLog constructor.
	 * @param string|bool $url
	 * @param string|bool $path
	 */
	public function __construct($url = false, $path = false) {
		if ($url) $url = " - " . $url;
		$this->start = microtime(true);
		$this->log = "\r\n" . date("Y-m-d H:i:s", $this->start) . $url . "\r\n";
		$this->path = ($path) ? $path : PERFLOGPATH;
		register_shutdown_function(array($this, 'Close'));
	}

	/** @param string $message */
	public function Log($message) {
		$this->log .= "\t+" . number_format(microtime(true) - $this->start, 4) . " seconds:\r\n";
		$this->log .= "\t\t" . str_replace("\r\n", "\r\n\t\t", $message) . "\r\n";
	}

	public function Close() {
		$this->log .= "\tCompleted: " . number_format(microtime(true) - $this->start, 4) . " seconds:\r\n";
		singleLog($this->log, $this->path);
	}
}