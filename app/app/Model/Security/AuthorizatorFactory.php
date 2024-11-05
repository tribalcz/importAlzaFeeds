<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Model\Security;

use Nette\Security\Permission;

class AuthorizatorFactory
{
	public static function create(): Permission
	{
		$acl = new Permission();
		$acl->addRole(Role::GUEST->value);
		$acl->addRole(Role::ADMINISTRATOR->value, 'guest');
		$acl->addResource('backend');

		$acl->allow(Role::ADMINISTRATOR->value, 'backend');

		return $acl;
	}
}