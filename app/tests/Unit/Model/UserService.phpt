<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Tests\Model;

$container = require __DIR__ . '/../../bootstrap.php';

use Doctrine\ORM\EntityManagerInterface;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;
use Price2Performance\Price2Performance\Model\ORM\Entity\User;
use Price2Performance\Price2Performance\Model\ORM\Services\UserService;
use Price2Performance\Price2Performance\Tests\ORMTestCase;
use Tester\Assert;


class UserServiceTest extends ORMTestCase
{
	protected UserService $userService;

	public function setUp()
	{
		parent::setUp();
		$passwords = $this->createContainer()->getByType(Passwords::class);
		$this->userService = new UserService($this->em, $passwords);
	}

	public function testFindUser()
	{
		$user = $this->userService->findUser('test@test.cz');
		Assert::type(User::class, $user);

		$user = $this->userService->findUser('unknown');
		Assert::null($user);
	}

	public function testAuthenticateCorrectValues()
	{
		$identity = $this->userService->authenticate('test@test.cz', 'test');
		Assert::type(SimpleIdentity::class, $identity);
		Assert::same(['administrator'], $identity->getRoles());
	}

	public function testAuthenticateIncorrectPassword()
	{
		Assert::exception(function () {
			$identity = $this->userService->authenticate('test@test.cz', 'wrong');
		}, AuthenticationException::class, 'Password not correct.');
	}

	public function testAuthenticateIncorrectUsername()
	{
		Assert::exception(function () {
			$identity = $this->userService->authenticate('wrong', 'wrong');
		}, AuthenticationException::class, 'User wrong not found.');
	}
}

(new UserServiceTest())->run();