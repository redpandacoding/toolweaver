<?php

namespace RedPandaCoding\ToolWeaver\Service\Shell;

trait ShellUtilTrait
{
    private ShellUtils $shellUtil;

    public function setMailer(ShellUtils $shell): void
    {
        $this->shellUtil = $shell;
    }

}