<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Console;

use Price2Performance\Price2Performance\Model\Utils\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{

	protected InputInterface $input;
	protected OutputInterface $output;
	protected float $start = 0.0;
	protected Logger $logger;

	protected function initialize(InputInterface $input, OutputInterface $output)
	{
		$this->input = $input;
		$this->output = $output;
		$this->start = microtime(true);
		$this->logger = new Logger();

		$this->setCode(function(InputInterface $input, OutputInterface $output): int {
			$statusCode = $this->execute($input, $output);
			$this->sendReport();
			return $statusCode;
		});
	}

	protected function log(string $message, string $level = Logger::INFO): void
	{
		$this->logger->log($message, $level);
		$this->output->writeln($message);
	}

	protected function sendReport(): void
	{
		$end = microtime(true);
		$this->log('Done in ' . number_format(($end - $this->start), 3) . ' seconds.');

		/*foreach (['deeni@seznam.cz', 'info@cena-vykon.cz'] as $to) {
			$this->communicator->mail(
				$to,
				$this->getName(),
				implode('<br>', $this->logger->getMessages()),
			);
		}*/
	}
}