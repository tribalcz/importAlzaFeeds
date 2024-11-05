<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Model\ORM\Entity;

abstract class AbstractEntity
{
	protected ?int $id = 0;

	public function getId(): int
	{
		return $this->id;
	}

}
