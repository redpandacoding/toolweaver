<?php
/**
 * @Author: jwamser
 * @CreateAt: 11/26/23
 * Project: ToolWeaver
 * File Name: Command.php
 */

namespace RedPandaCoding\ToolWeaver\Command;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RedPandaCoding\ToolWeaver\Service\Shell\ShellUtils;
use RedPandaCoding\ToolWeaver\ToolWeaverApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class Command extends SymfonyCommand// implements ServiceSubscriberInterface
{
    protected function isToolWeaverApplication(): bool
    {
        if (class_exists(ToolWeaverApplication::class)) {
            return $this->getApplication() instanceof ToolWeaverApplication;
        }

        return false;
    }

//    /**
//     * @throws ContainerExceptionInterface
//     * @throws NotFoundExceptionInterface
//     */
//    protected function get(string $id)
//    {
//        if (!$this->locator->has($id)) {
//            throw new \Exception(sprintf(
//                'Service `%s` not found!',
//                $id
//            ));
//        }
//
//        return $this->locator->get($id);
//    }

//    public static function getSubscribedServices(): array
//    {
//        return [];
//    }
}