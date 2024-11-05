<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Model\ORM\Entity;

use Price2Performance\Price2Performance\Model\Security\Role;
use Nette\Utils\Random;

class User extends AbstractEntity
{
	protected string $confirmationHash;
	protected \DateTime|null $confirmationDate = null;

	public function __construct(
		protected string $email,
		protected string $password,
		protected Role $role = Role::REGISTERED,
	) {
		$this->regenerateConfirmationHash();
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	public function setPassword(string $password): void
	{
		$this->password = $password;
	}

	public function getRole(): Role
	{
		return $this->role;
	}

	public function setRole(Role $role): void
	{
		$this->role = $role;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): void
	{
		$this->email = $email;
	}

	public function getConfirmationDate(): \DateTime|null
	{
		return $this->confirmationDate;
	}

	public function validateHash(string $hash): bool
	{
		return $hash === $this->confirmationHash;
	}

	public function isConfirmed(): bool
	{
		return $this->confirmationDate === null;
	}

	public function regenerateConfirmationHash(): void
	{
		$this->confirmationHash = Random::generate(12, 'a-zA-Z0-9');
	}

	public function makeConfirmed(): void
	{
		if (!$this->isConfirmed())
			$this->confirmationDate = new \DateTime();
	}
}