<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->withModule('Admin')->addRoute('administrace/<presenter>/[<action>/][<id>/]', 'Sign:default');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
		return $router;
	}
}
