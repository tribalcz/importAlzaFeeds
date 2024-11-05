<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Forms;

use Nette\Application\UI\Form;
use Nette\Security\User;
use Price2Performance\Price2Performance\Model\ORM\Lists\SelectableList;

final class FormFactory
{

	public function __construct(
		private User $user,
	) {}

	public function create(): Form
	{
		$form = new Form();

		if ($this->user->isLoggedIn())
			$form->addProtection();

		return $form;
	}

	public function createFromClass(string $className): Form
	{
		$form = $this->create();

		$entity = new \ReflectionClass($className);

		foreach ($entity->getProperties() as $property) {
			if ($property->getType()->getName() === 'string')
				$form->addText($property->getName(), $property->getName());
			elseif ($property->getType()->getName() === 'bool')
				$form->addCheckbox($property->getName(), $property->getName());
			elseif (enum_exists($property->getType()->getName()) && in_array(SelectableList::class, class_uses($property->getType()->getName()))) {
				$enum = $property->getType()->getName();
				$items = $enum::forSelect();
				$form->addSelect($property->getName(), $property->getName(), $items);
			}
		}

		$form->addSubmit('send', 'Uložit');

		return $form;
	}
}