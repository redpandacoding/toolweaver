<?php
/**
 * @Author: jwamser
 * @CreateAt: 11/26/23
 * Project: ToolWeaver
 * File Name: ShellUtils.php
 */

namespace RedPandaCoding\ToolWeaver\Service\Shell;

use Symfony\Component\Filesystem\Filesystem;

class ShellUtils
{
    private ?Filesystem $fs;

    public function __construct(bool $preferRsync = false)
    {
        $this->fs = $preferRsync ? null : new Filesystem();
    }

    private function mkdir(string $path): void
    {
        if (null === $this->fs) {
            $this->exec(sprintf('mkdir -p %s', $path));
            return;
        }

        $this->fs->mkdir($path);
    }

    private function rsyncFile(string $file, ?string $destination = null,bool $noClobber = true): void
    {
        if (null === $this->fs) {
            if ($destination === 'build' and $this->isSy) {
                // This folder is not used for Symfony setup
                return;
            }
            if (empty($destination)) {
                $destination = $file;
            }

            $this->exec(sprintf('rsync -a %s %s/%s %s',
                $noClobber ? '--ignore-existing' : '',
                $this->templateDir,
                $file,
                $destination
            ));
        }

    }

    private function rm(string $path): void
    {
        if (null === $this->fs) {
            $this->exec('rm -rf '.$path);
            return;
        }

        $this->fs->remove($path);
    }

    private function exec(string $command): void
    {
        var_dump('AHHH this still needs setup');die;
//        exec($command);
    }

    private function sedSearchReplaceInFile(string $file, string $original, string $replacement): void
    {
        if (null === $this->fs) {
            $this->exec("sed -i '' 's/$original/$replacement/g' $file");
        }

    }

    private function searchReplaceTextInDirectory(string $directory, string $original, string $replacement): void
    {
        if (null === $this->fs) {
            $this->exec("grep -rl $original $directory | xargs sed -i '' 's/$original/$replacement/g'");
        }

    }
}