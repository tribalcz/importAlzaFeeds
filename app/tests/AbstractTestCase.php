<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Tests;

use Nette\DI\Container;
use Price2Performance\Price2Performance\Bootstrap;
use Tester\TestCase;

abstract class AbstractTestCase extends TestCase
{

	public function createContainer(): Container
	{
		return Bootstrap::bootForTests()->createContainer();
	}
}