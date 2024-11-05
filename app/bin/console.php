#!/usr/bin/env php
<?php declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

define('TEMP_DIR', __DIR__ . '/../temp');

exit(\Price2Performance\Price2Performance\Bootstrap::bootForCli()
	->createContainer()
	->getByType(Contributte\Console\Application::class)
	->run());