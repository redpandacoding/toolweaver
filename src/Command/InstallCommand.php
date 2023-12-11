<?php
/**
 * @Author: jwamser
 * @CreateAt: 11/26/23
 * Project: ToolWeaver
 * File Name: InstallCommand.php
 */

namespace RedPandaCoding\ToolWeaver\Command;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RedPandaCoding\ToolWeaver\Service\Shell\ShellUtils;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ServiceLocator;

#[AsCommand(
    name: 'install',
    description: 'Install all test packages',
)]
class InstallCommand extends Command
{
    public function __construct(
        private readonly ShellUtils $shell,
    ){
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setHelp('This command helps you to install all of the configured test packages and make sure they are configured')
            ->addArgument(
                'directory',
                InputArgument::OPTIONAL,
//                $this->isToolWeaverApplication() ? InputArgument::OPTIONAL : InputArgument::REQUIRED,
                'The directory location of the template files.',
                null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input,$output);
        $templateDirectory = $input->getArgument('directory');
        if (null === $templateDirectory && $this->isToolWeaverApplication()) {
            // we are in ToolWeaver lets see if we can figure out the template directory
            // This is in the init of the ToolWeaver Application that discovers what project type
            $templateDirectory = $this->getApplication()->getTemplateDirectory() ?? null;

            if (!is_dir($templateDirectory)) {
                $io->error('Tool Weaver was unable to figure out what kind of project you are working in.');
                return self::FAILURE;
            }
        }

        // Create File in project from the template folder.
        $this->install();

        return self::SUCCESS;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function install(): void
    {
        // NodeJS version file
        $this->shell->rsyncFile('.node-version',$this->getApplication()->getTemplateDirectory());

        //

        // original script
//        $this->rsyncFileSafely('.node-version');
//        $this->rsyncFileSafely('Makefile');
//
//        $this->setupGithubWorkflows();
//        $this->setupGitHook();
//
//        $this->setupComposerPackages();
//
//        $this->rsyncFileSafely('build/', 'build');
//
//        $this->wpSpecificReplacements();
//        $this->sySpecificReplacements();
    }

    private function rsyncFile($file,)
    {

    }

}