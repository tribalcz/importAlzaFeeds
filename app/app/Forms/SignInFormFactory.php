<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Forms;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

final class SignInFormFactory
{

	public function __construct(
		private FormFactory $factory,
		private User $user
	) {}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->addText('email', 'E-mail')
			->setRequired('Zadejte prosím přihlašovací e-mail.');

		$form->addPassword('password', 'Heslo')
			->setRequired('Zadejte prosím heslo.');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess): void {
			try {
				$this->user->login($data->email, $data->password);
			} catch (AuthenticationException $e) {
				$form->addError('Zadaná kombinace e-mailu a hesla není správná.');
				return ;
			}

			$onSuccess();
		};

		return $form;
	}
}