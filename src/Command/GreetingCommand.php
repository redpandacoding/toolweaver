<?php
/**
 * @Author: jwamser
 * @CreateAt: 11/26/23
 * Project: ToolWeaver
 * File Name: TimeCommand.php
 */

namespace RedPandaCoding\ToolWeaver\Command;

use DateTime;
use DateTimeZone;
use Exception;
use RedPandaCoding\ToolWeaver\Service\Shell\ShellUtils;
use RedPandaCoding\ToolWeaver\ToolWeaverApplication;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'greet',
    description: 'Greet a user based on the time of the day.',

)]
class GreetingCommand extends Command
{

    public function __construct(
        private ShellUtils $shell,
    ){
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            -> setHelp('This command allows you to greet a user based on the time of the day...')
//            -> addArgument('username', InputArgument::, 'The username of the user.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
//        var_dump($this->getApplication()->get('weaver.tools.shell_util'));
        $output->writeln($this->getGreeting().', '.get_current_user());

        return self::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function getGreeting()
    {
        /* This sets the $time variable to the current hour in the 24 hour clock format */
        $time = date("H",(new DateTime('now',new DateTimeZone('America/Chicago')))->getTimestamp());
        /* Set the $timezone variable to become the current timezone */

        /* If the time is less than 1200 hours, show good morning */
        if ($time < "12") {
            return "Good morning";
        } else
        /* If the time is grater than or equal to 1200 hours, but less than 1700 hours, so good afternoon */
        if ($time >= "12" && $time < "17") {
            return "Good afternoon";
        } else
        /* Should the time be between or equal to 1700 and 1900 hours, show good evening */
        if ($time >= "17" && $time < "19") {
            return "Good evening";
        } else
        /* Finally, show good night if the time is greater than or equal to 1900 hours */
        if ($time >= "19") {
            return "Good night";
        }
    }
}