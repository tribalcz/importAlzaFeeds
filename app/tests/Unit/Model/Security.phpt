<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Tests\Unit\Model;

$container = require __DIR__ . '/../../bootstrap.php';

use Nette\Security\User;
use Price2Performance\Price2Performance\Tests\ORMTestCase;
use Tester\Assert;


class SecurityTest extends ORMTestCase
{

	protected User $user;

	public function setUp()
	{
		parent::setUp();
		$this->user = $this->createContainer()->getByType(User::class);
	}

	public function testAuthenticatorAndAuthorizator()
	{
		$user = $this->user;
		Assert::false($user->isLoggedIn());
		Assert::false($user->isAllowed('backend'));

		$user->login('test@test.cz', 'test');
		Assert::true($user->isLoggedIn());
		Assert::true($user->isAllowed('backend'));
	}
}

(new SecurityTest())->run();