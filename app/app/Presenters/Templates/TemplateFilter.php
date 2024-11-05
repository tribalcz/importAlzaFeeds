<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Presenters\Templates;

class TemplateFilter
{

	// {$price|displayPrice} -> 1 234,56 Kč
	public static function displayPrice(int|float|string $price): string
	{
		return number_format((float) $price, 2, ',', ' ') . ' Kč';
	}

	public static function register(\Nette\Application\UI\Template $template)
	{
		$reflection = new \ReflectionClass(self::class);

		foreach ($reflection->getMethods(\ReflectionMethod::IS_STATIC) as $method) {
			if ($method->getName() === 'register')
				continue;
			$template->addFilter($method->getName(), [$method->getDeclaringClass()->getName(), $method->getName()]);
		}
	}

}