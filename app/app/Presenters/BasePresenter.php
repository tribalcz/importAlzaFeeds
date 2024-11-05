<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Presenters;

use Nette\Application\Helpers;
use Nette\Application\UI\Presenter;
use Price2Performance\Price2Performance\Presenters\Templates\TemplateFilter;

class BasePresenter extends Presenter
{
	public function injectTemplateFilter(): void
	{
		$this->onStartup[] = function () {
			TemplateFilter::register($this->template);
			$this->template->tempDir = TEMP_DIR;
		};
	}

	public function formatTemplateFiles(): array
	{
		[, $presenter] = Helpers::splitName($this->getName());
		$dir = dirname(static::getReflection()->getFileName());
		$dir = is_dir("$dir/Templates") ? $dir : dirname($dir);
		return [
			"$dir/Templates/$presenter/$this->view.latte",
			"$dir/Templates/$presenter.$this->view.latte",
		];
	}

	public function formatLayoutTemplateFiles(): array
	{
		if (preg_match('#/|\\\\#', (string) $this->layout)) {
			return [$this->layout];
		}

		[$module, $presenter] = Helpers::splitName($this->getName());
		$layout = $this->layout ?: 'layout';
		$dir = dirname(static::getReflection()->getFileName());
		$dir = is_dir("$dir/Templates") ? $dir : dirname($dir);
		$list = [
			"$dir/Templates/$presenter/@$layout.latte",
			"$dir/Templates/$presenter.@$layout.latte",
		];
		do {
			$list[] = "$dir/Templates/@$layout.latte";
			$dir = dirname($dir);
		} while ($dir && $module && ([$module] = Helpers::splitName($module)));

		return $list;
	}
}