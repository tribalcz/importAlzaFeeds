<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Model\Utils;

class Logger
{
	public const EMERGENCY = 'emergency';
	public const ALERT = 'alert';
	public const CRITICAL = 'critical';
	public const ERROR = 'error';
	public const WARNING = 'warning';
	public const NOTICE = 'notice';
	public const INFO = 'info';
	public const DEBUG = 'debug';

	private array $messages = [];

	private array $levels = [];

	public function clear(): void
	{
		$this->messages = [];
		$this->levels = [];
	}

	public function getMessages(): array
	{
		return $this->messages;
	}

	public function getLevels(): array
	{
		return $this->levels;
	}

	public function filterMessages(array $levels): array
	{
		$filtered = [];

		foreach ($this->messages as $key => $message) {
			$level = $this->levels[$key];
			if (!array_key_exists($level, $filtered))
				$filtered[$level] = [];
			if (in_array($level, $levels)) {
				$filtered[$level][$key] = $message;
			}
		}

		return $filtered;
	}

	public function log($message, $level = Logger::INFO, array $context = []): void
	{
		if (!in_array($level, [Logger::EMERGENCY, Logger::ALERT, Logger::CRITICAL, Logger::ERROR, Logger::WARNING, Logger::NOTICE, Logger::INFO, Logger::DEBUG])) {
			throw new \InvalidArgumentException('Invalid log level');
		}

		$now = new \DateTime();
		$m = $now->format('H:i:s.u') . ': ' . $message;
		$newKey = count($this->messages);
		$this->messages[$newKey] = $m;
		$this->levels[$newKey] = $level;
	}
}