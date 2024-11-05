<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Model\ORM\Lists;

trait SelectableList
{

	public static function forSelect(): array
	{
		return array_combine(array_column(self::cases(), 'name'), array_column(self::cases(), 'value'));
	}
}