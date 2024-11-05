<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Admin\Presenters;

trait RequireLoggedUser
{
	public function injectRequireLoggedUser(): void
	{
		$this->onStartup[] = function () {
			if (!$this->getUser()->isLoggedIn()) {
				$this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
			}

			$this->setLayout('admin');
		};
	}
}