<?php declare(strict_types=1);

namespace Price2Performance\Price2Performance\Console;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Generator extends BaseCommand
{
    protected static $defaultName = 'app:generate:presenter';
    protected string $presenterDir;
    protected string $templateDir;
    protected string $factoryDir;
    protected string $repositoryDir;

    public function __construct(string $presenterDir, string $templateDir, string $factoryDir, string $repositoryDir)
    {
        parent::__construct();
        $this->presenterDir = $presenterDir;
        $this->templateDir = $templateDir;
        $this->factoryDir = $factoryDir;
        $this->repositoryDir = $repositoryDir;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate presenter with template, template and factory');
        $this->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name of the presenter');
        $this->addOption('module', null, InputOption::VALUE_OPTIONAL, 'Name of the module');
        $this->addOption('actions', null, InputOption::VALUE_OPTIONAL, 'Actions of the presenter');
        $this->addOption('factory', null, InputOption::VALUE_NONE, 'Factory of the presenter');
        $this->addOption('repository', null, InputOption::VALUE_NONE, 'Repository of the presenter');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getOption('name');
        if (!$name) {
            $io->error('You must specify the presenter name using --name');
            return Command::FAILURE;
        }

        $module = $input->getOption('module');
        $actions = $input->getOption('actions') ? explode(',', $input->getOption('actions')) : ['default'];
        $createFactory = $input->getOption('factory');
        $createRepository = $input->getOption('repository');

        $namespace = $module ? "App\\Presenters\\{$module}" : 'App\\Presenters';
        $presenterPath = $this->presenterDir . '/';
        $factoryPath = $this->factoryDir . '/';
        $repositoryPath = $this->repositoryDir . '/';

        if ($module) {
            $presenterPath .= $module . '/';
        }
        FileSystem::createDir($presenterPath);

        //Presenter generation
        $presenterContent = $this->generatePresenterContent($namespace, $name, $actions, $createFactory);
        FileSystem::write($presenterPath . $name . 'Presenter.php', $presenterContent);

        //Template generation
        foreach ($actions as $action) {
            $templatePath = $this->templateDir . '/' . ($module ? $module . '/' : '') . $name . '/' . $action . '.latte';
            FileSystem::createDir(dirname($templatePath));
            FileSystem::write($templatePath, $this->generateTemplateContent($action));
        }
        $io->success('Template ' . $action . ' was successfully generated, in ' . $this->templateDir . '/' . ($module ? $module . '/' : '') . $name);

        //Factory generation
        if ($createFactory) {
            $factoryContent = $this->generateFactoryContent($namespace, $name);
            FileSystem::write($factoryPath . $name . 'Factory.php', $factoryContent);
            $io->success('Factory ' . $name . ' was successfully generated, in ' . $factoryPath);
        }

        //Repository generation
        if ($createRepository) {
            $repositoryContent = $this->generateRepositoryContent($namespace, $name);
            FileSystem::write($repositoryPath . $name . 'Repository.php', $repositoryContent);
            $io->success('Repository ' . $name . ' was successfully generated, in ' . $repositoryPath);
        }

        $io->success('Presenter ' . $name . ' was successfully generated, in ' . $presenterPath);
        return Command::SUCCESS;
    }

    private function generatePresenterContent(string $namespace, string $name, array $actions, bool $hasFactory): string
    {
        $template = <<<PHP
<?php declare(strict_types=1);

namespace {$namespace};

use Nette;

class {$name}Presenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private readonly {$name}Repository \${$name}Repository
    )
    {
        parent::__construct();
    }

PHP;

        foreach ($actions as $action) {
            $actionMethod = Strings::firstUpper($action);
            $template .= <<<PHP
    public function action{$actionMethod}(): void
    {
        //TODO: Implement action
    }
    
    public function render{$actionMethod}(): void
    {
        //TODO: Prepare data for template
    }

PHP;
        }

        $template .= "}\n";
        return $template;
    }

    private function generateTemplateContent(string $action): string
    {
        return <<<LATTE
{block content}
    <h1>{$action} action</h1>

{* TODO: Implement the template *}
LATTE;
    }

    private function generateFactoryContent(string $namespace, string $name): string
    {
        return <<<PHP
<?php declare(strict_types=1);

namespace {$namespace};

use Nette;

class {$name}Factory
{
    public function __construct(
        private readonly Nette\DI\Container \$container
    )
    {
    }

    public function create(): {$name}Presenter
    {
        return \$this->container->createInstance({$name}Presenter::class);

    }
}
PHP;
    }

    private function generateRepositoryContent(string $namespace, string $name): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use Nette;

class {$name}Repository
{
    public function __construct(
        private readonly Nette\Database\Explorer \$database
    ) {
    }

    // TODO: Implement repository methods
}
PHP;
    }
}