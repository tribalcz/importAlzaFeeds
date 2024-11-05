<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Console;

use Price2Performance\Price2Performance\Model\ORM\Services\UserService;
use Nette\DI\Attributes\Inject;
use Nette\Utils\Random;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends BaseCommand
{

	#[Inject]
	public UserService $userService;

	protected static $defaultName = 'sandbox:create-user';

	protected static $defaultDescription = 'Creates a new user';

	protected function configure()
	{
		parent::configure();
		$this->addArgument('email', InputArgument::REQUIRED, 'E-mail');
		$this->addArgument('role', InputArgument::REQUIRED, 'Role');
		$this->addArgument('password', InputArgument::OPTIONAL, 'Password');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$password = $input->getArgument('password') ?? Random::generate(16, 'a-zA-Z0-9');
		$email = $input->getArgument('email');
		$role = $input->getArgument('role');

		$user = $this->userService->registerUser($email, $password, $role);

		if ($user !== null)
			$this->log('User ' . $email . ' created successfully with password ' . $password . ', role: ' . $role);

		return Command::SUCCESS;
	}
}