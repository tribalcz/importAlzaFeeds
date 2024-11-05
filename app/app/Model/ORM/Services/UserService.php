<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Model\ORM\Services;

use Doctrine\ORM\EntityManagerInterface;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;
use Price2Performance\Price2Performance\Model\ORM\Entity\User;
use Price2Performance\Price2Performance\Model\Security\Role;

class UserService extends AbstractService implements Authenticator
{

	public function __construct(
		protected EntityManagerInterface $em,
		protected Passwords $passwords,
	)
	{}

	public function registerUser(string $email, string $password, string|Role $role = Role::REGISTERED): User
	{
		if (is_string($role))
			$role = Role::from($role);

		$user = new User($email, $this->passwords->hash($password));
		$user->setRole($role);

		$this->em->persist($user);
		$this->em->flush();

		return $user;
	}

	public function findUser(string $email): ?User
	{
		return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
	}

	public function getUserById(int $id): ?User
	{
		return $this->em->getRepository(User::class)->find($id);
	}

	public function authenticate(string $user, string $password): IIdentity
	{
		$userEntity = $this->findUser($user);

		if ($userEntity === null)
			throw new AuthenticationException('User ' . $user . ' not found.');

		if (!$this->passwords->verify($password, $userEntity->getPassword()))
			throw new AuthenticationException( 'Password not correct.');

		return new SimpleIdentity($userEntity->getId(), $userEntity->getRole()->value, ['email' => $userEntity->getEmail(), 'id' => $userEntity->getId()]);
	}
}