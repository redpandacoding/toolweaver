<?php

/*
 * This file is part of the ToolWeaver package.
 *
 * (c) Jordan Wamser <jwamser@redpandacoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RedPandaCoding\ToolWeaver;

use Redpandacoding\Contracts\Exceptions\LogicException;
use RedPandaCoding\ToolWeaver\Command\InstallCommand;
use RedPandaCoding\ToolWeaver\Command\GreetingCommand;
use RedPandaCoding\ToolWeaver\Service\Shell\ShellUtils;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use Redpandacoding\Contracts\Console\KernelInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @author Jordan Wamser <jwamser@redpandacoding.com>
 **/
class Kernel implements KernelInterface
{
    protected ?ContainerInterface $container = null;
    protected bool $booted = false;

    public const VERSION = '1.0.0-DEV';
    public const VERSION_ID = 10000;
    public const MAJOR_VERSION = 1;
    public const MINOR_VERSION = 0;
    public const RELEASE_VERSION = 0;
    public const EXTRA_VERSION = 'DEV';

    public function boot(): void
    {
        if (true === $this->booted) {
            return;
        }

        if (null === $this->container) {
            $this->preBoot();
        }

// TODO: This will be implemented later as this will be how we extend this console application
//        foreach ($this->getBundles() as $bundle) {
//            $bundle->setContainer($this->container);
//            $bundle->boot();
//        }

        $this->booted = true;
    }

    public function getContainer(): \Symfony\Component\DependencyInjection\ContainerInterface
    {
        if (!$this->container) {
            throw new LogicException('Cannot retrieve the container from a non-booted kernel.');
        }

        return $this->container;
    }

    public function shutdown(): void
    {
        if (false === $this->booted) {
            return;
        }

        $this->booted = false;

// TODO: This will be implemented later as this will be how we extend this console application
//        foreach ($this->getBundles() as $bundle) {
//            $bundle->shutdown();
//            $bundle->setContainer(null);
//        }

        $this->container = null;
    }

    /**
     * @note Idea based from https://github.com/symfony/http-kernel/blob/4190c75a17b40ae94ec739e2c5ca55dce731cf39/Kernel.php#L711-L737
     */
    private function preBoot(): ContainerInterface
    {
        if (!isset($_ENV['SHELL_VERBOSITY']) && !isset($_SERVER['SHELL_VERBOSITY'])) {
            if (\function_exists('putenv')) {
                putenv('SHELL_VERBOSITY=3');
            }
            $_ENV['SHELL_VERBOSITY'] = 3;
            $_SERVER['SHELL_VERBOSITY'] = 3;
        }

//        $this->initializeBundles();
        $this->initializeContainer();

        return $this->container;
    }

    /**
     * Initializes the service container.
     *
     * @note Idea from https://github.com/symfony/http-kernel/blob/4190c75a17b40ae94ec739e2c5ca55dce731cf39/Kernel.php#L386-L532
     */
    protected function initializeContainer(): void
    {
        // TODO: when we add extensions/bundles this call will need to be $this->getContainerBuilder(); https://github.com/symfony/http-kernel/blob/4190c75a17b40ae94ec739e2c5ca55dce731cf39/Kernel.php#L630-L643
        // Create a ContainerBuilder instance
        $container = new ContainerBuilder();

        $container->register('kernel')->setSynthetic(true);

        //Commands
        $container->register(GreetingCommand::class,GreetingCommand::class)
            ->setAutowired(true)
            ->setArguments([new Reference(ShellUtils::class)])
            ->addTag('console.command')
        ;

        $container->register(InstallCommand::class,InstallCommand::class)
            ->setAutowired(true)
//            ->setArguments([new Reference(ShellUtils::class)])
            ->addTag('console.command')
        ;

        // Define and register services
        $container->register(ShellUtils::class,ShellUtils::class)
            ->setAutowired(true)
        ;

        // Application
        $container->register('tool',ToolWeaverApplication::class)
            ->addMethodCall('setSubscribedCommands',[new TaggedIteratorArgument('console.command')])
            ->setPublic(true)
            ->addArgument(new Reference('kernel'))
//            ->setSynthetic(true)
        ;

        $container->compile();
        $this->container = $container;

        $this->container->set('kernel', $this);

//        var_dump($this->container->has('weaver.tools.shell_util'));//returns false

    }

    public function run()
    {
        $this->boot();

        return $this->getContainer()->get('tool');
    }
}