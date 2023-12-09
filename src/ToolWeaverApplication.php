<?php
/**
 * @Author: jwamser
 * @CreateAt: 11/26/23
 * Project: ToolWeaver
 * File Name: ToolWeaverApplication.php
 */

namespace RedPandaCoding\ToolWeaver;

use RedPandaCoding\ToolWeaver\Command\InstallCommand;
use RedPandaCoding\ToolWeaver\Command\TimeCommand;
use RedPandaCoding\ToolWeaver\Service\Shell\ShellUtils;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Path;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ToolWeaverApplication extends Application
{
    public const NAME = 'Tool Weaver';
    public const VERSION = '1.0.0';
    public const VERSION_ID = 10000;
    public const MAJOR_VERSION = 1;
    public const MINOR_VERSION = 0;
    public const RELEASE_VERSION = 0;
    public const EXTRA_VERSION = '';

    public ContainerInterface $container;

    /**
     * Whether to overwrite existing build process files
     * By default it will NOT overwrite existing files in cases where someone has altered them for the project
     *
     * @var bool
     */
    private bool $noClobber = true;

    /**
     * Whether or not to use RSYNC over Symfony FileSystem->mirror().
     * @note While Symfony's mirror() method offers a convenient way to copy directories within a PHP application, it's more suitable for simpler or smaller-scale file copying tasks. If you need the advanced features, performance, and efficiency of rsync, especially for large or remote file systems, using rsync directly would be a better choice.
     *
     * @var bool
     */
    private bool $rsync = false;

    /**
     * Return codes
     */
    private const RUNNING = -1;
    private const SUCCESS = 0;
    private const WITH_ERRORS = 1;
    private const FAILED = 254;

    /**
     * The directory internal to this package that we will be using to grab our templates from
     * @todo Add add config to over write init setup location.
     *
     * @var string
     */
    private string $templateDir;

    private string $configDirectory = DIRECTORY_SEPARATOR.'build';

    /**
     * The current working directory, using getcwd() vs $_SERVER['PWD']
     * @note Use getcwd() for a more universally accurate and environment-independent way of getting the current working directory in PHP scripts.
     * @note $_SERVER['PWD'] can be used in CLI scripts where you are certain of the environment and when you need the directory from where the script was called, rather than where it's currently running. However, be cautious of its reliability and availability in different environments.
     * @todo Add config options in constructor to all user to chose which method they would like to use.
     *
     * @var string|false
     */
    private string|false $cwd;

    public function __construct(bool $art = true)
    {
        parent::__construct(self::NAME, self::VERSION);
        if ($art) {
            $this->addAsciiArt();
        }

        $this->serviceWiring();
        $this->init();

        $this->cwd = getcwd();

        $this->templateDir = Path::join(
            dirname(__DIR__),
            'templates',
            $this->isWordpressProject() ? 'Wordpress' : ($this->isSymfonyProject() ? 'Symfony' : '')
        );

        //Symfony doesn't by default setup things in the `./build` directory, if flex is installed this will cause issues
        if (class_exists(Symfony\Flex\Flex::class)) {
            // @TODO this one could be more problemative. i want to check if that file exists in the project im installing into not my project thats doing the actions
            $this->configDirectory = '';
        }
    }

    public function getTemplateDirectory()
    {
        return $this->templateDir;
    }

    public function isSymfonyProject(): bool
    {
        $projectType = is_file(getcwd().'/symfony.lock');

        return $projectType;
    }

    public function isWordpressProject(): bool
    {
        $projectType = str_contains(getcwd(), '/wp-content');

        return $projectType;
    }

    private function addAsciiArt()
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) {
            $output = $event->getOutput();
            $outputStyle = new OutputFormatterStyle('#ff7400', 'white', ['bold', 'blink']);
            $output->getFormatter()->setStyle('tool', $outputStyle);
            // ASCII art to display before every command
            $asciiArt = [
                '<tool>████████╗░█████╗░░█████╗░██╗░░░░░     ██╗░░░░██╗███████╗░█████╗░██╗░░░██╗███████╗██████╗░</>',
                '<tool>╚══██╔══╝██╔══██╗██╔══██╗██╗░░░░░     ██║░░░░██║██╔════╝██╔══██╗██║░░░██║██╔════╝██╔══██╗</>',
                '<tool>░░░██║░░░██║░░██║██║░░██║██╗░░░░░     ██║░█╗░██║█████╗░░███████║██║░░░██║█████╗░░██████╔╝</>',
                '<tool>░░░██║░░░██║░░██║██║░░██║██╗░░░░░     ██║███╗██║██╔══╝░░██╔══██║╚██╗░██╔╝██╔══╝░░██╔══██╗</>',
                '<tool>░░░██║░░░╚█████╔╝╚█████╔╝███████╗     ╚███╔███╔╝███████╗██║░░██║░╚████╔╝░███████╗██║░░██║</>',
                '<tool>░░░╚═╝░░░░╚════╝░░╚════╝░╚══════╝     ░╚══╝╚══╝░╚══════╝╚═╝░░╚═╝░░╚██╔╝░░╚══════╝╚═╝░░╚═╝</>',
            ];

            $output->writeln($asciiArt);
        });
        $this->setDispatcher($dispatcher);
    }

    private function init()
    {

        // Register commands with their specific service locators
        foreach (self::getSubscribedCommands() as $commandClass) {
            if (is_subclass_of($commandClass, ServiceSubscriberInterface::class)) {
                $subscribedServices = call_user_func([$commandClass, 'getSubscribedServices']);
                $locator = new ServiceLocator(array_map(function ($id) {
                    return function () use ($id) { return $this->container->get($id); };
                }, array_flip($subscribedServices)));

                /** @var \Symfony\Component\Console\Command\Command $command */
                $command = new $commandClass($locator);

                $this->add($command);
                continue;
            }

            $this->add(new $commandClass());
        }
    }

    private function serviceWiring(): void
    {
        // Create and configure the container
        $this->container = new ContainerBuilder();

        // Parameters
        $this->container->setParameter('tools.locator', ServiceLocator::class);

        // Services
        $this->container->autowire('weaver.tool.shellutil',ShellUtils::class);
        $this->container->autowire('%tools.locator%',ServiceLocator::class);

        // Commands
        $this->container->autowire('weaver.cmd.install',InstallCommand::class)
            ->addArgument([new Reference('%tools.locator%')]);

        $this->container->compile();
    }

    private static function getSubscribedCommands(): array
    {
        return [
            InstallCommand::class,
            TimeCommand::class,
        ];
    }
}