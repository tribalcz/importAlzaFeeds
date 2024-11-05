<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Tests\Unit;

$container = require __DIR__ . '/../bootstrap.php';

use Nette\DI\Container;
use Price2Performance\Price2Performance\Tests\AbstractTestCase;
use Tester\Assert;

class TestingTest extends AbstractTestCase
{
	public function testTestingWorks()
	{
		Assert::true(true);
	}

	public function testCreateContainer()
	{
		Assert::type(Container::class, $this->createContainer());
	}
}

(new TestingTest())->run();