<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Admin\Presenters;

use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;
use Price2Performance\Price2Performance\Forms\SignInFormFactory;
use Price2Performance\Price2Performance\Presenters\BasePresenter;

final class SignPresenter extends BasePresenter
{
	#[Persistent]
	public string $backlink = '';

	#[Inject]
	public SignInFormFactory $signInFactory;

	protected function createComponentSignInForm(): Form
	{
		return $this->signInFactory->create(function (): void {
			$this->restoreRequest($this->backlink);
			$this->redirect('Dashboard:');
		});
	}

	public function actionDefault(): void
	{
		$this->redirect('in');
	}

	public function actionIn(): void
	{
		if ($this->getUser()->isLoggedIn())
			$this->redirect('Dashboard:');
	}

	public function actionOut(): void
	{
		$this->getUser()->logout();
	}
}