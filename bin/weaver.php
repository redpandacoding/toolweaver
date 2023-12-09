#!/usr/bin/env php
<?php

use RedPandaCoding\ToolWeaver\Command\InstallCommand;
use RedPandaCoding\ToolWeaver\Service\Shell\ShellUtils;
use RedPandaCoding\ToolWeaver\ToolWeaverApplication;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Determine vendor directory.
 */
$vendorDirectory = '';

if (file_exists(__DIR__.'/../../autoload.php')) {
    $vendorDirectory = __DIR__.'/../..';
} elseif (file_exists(__DIR__.'/vendor/autoload.php')) {
    $vendorDirectory = __DIR__.'/vendor';
} elseif (file_exists(__DIR__.'/../vendor/autoload.php')) {
    $vendorDirectory = __DIR__.'/../vendor';
}

if (empty($vendorDirectory)) {
    throw new \RuntimeException('Unable to find vendor directory');
}

require $vendorDirectory.'/autoload.php';

$container = new ContainerBuilder();

// Load manual parameters
$container->setParameter('application_directory', __DIR__);
$container->setParameter('home_directory', rtrim(getenv('HOME'), '/'));
$container->setParameter('vendor_directory', $vendorDirectory);
$container->setParameter('working_directory', rtrim(getcwd(), '/'));
$container->setParameter('tools.locator', ServiceLocator::class);

// Application
$container->autowire('app',ToolWeaverApplication::class);

// Services
$container->autowire('weaver.tool.shellutil',ShellUtils::class);
$container->autowire('%tools.locator%',ServiceLocator::class);

// Commands
$container->autowire('weaver.cmd.install',InstallCommand::class)
    ->addArgument([new Reference('%tools.locator%')]);

// Compile container
$container->compile();

// Start the console application.
exit($container->get('app')->run());
