<?php

namespace Bundle\DeployBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DeployCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('server', InputArgument::REQUIRED, 'The server name'),
            ))
            ->addOption('go', null, InputOption::PARAMETER_NONE, 'Do the deployment')
            ->addOption('rsync-options', null, InputOption::PARAMETER_OPTIONAL, 'To options to pass to the rsync executable', '-azC --force --delete --progress')
            ->addOption('rsync-dir', null, InputOption::PARAMETER_REQUIRED, 'The directory where to look for rsync*.txt files', 'app/config')
            ->setName('project:deploy')
        ;
    }
    
    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env    = $input->getArgument('server');
        if(!$this->container->getParameter('deploy.'.$env.'.host')){
            throw new \InvalidArgumentException('You must provide the host (e.g. http://example.com)');
        }
        $host   = $this->container->getParameter('deploy.'.$env.'.host');
        $dir    = $this->container->getParameter('deploy.'.$env.'.dir');
        $user   = $this->container->getParameter('deploy.'.$env.'.user');
        $port   = $this->container->getParameter('deploy.'.$env.'.port');
                
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }
        
        $ssh = 'ssh';

        if ($port) {
          $ssh = '"ssh -p'.$port.'"';
        }
        
        if ($this->container->getParameter('deploy.'.$env.'.parameters')) {
          $parameters = $this->container->getParameter('deploy.parameters');
        } else {
          $parameters = $input->getOption('rsync-options');
          if (file_exists($input->getOption('rsync-dir').'/rsync_exclude.txt')) {
            $parameters .= sprintf(' --exclude-from=%s/rsync_exclude.txt', $input->getOption('rsync-dir'));
          }

          if (file_exists($input->getOption('rsync-dir').'/rsync_include.txt')) {
            $parameters .= sprintf(' --include-from=%s/rsync_include.txt', $input->getOption('rsync-dir'));
          }

          if (file_exists($input->getOption('rsync-dir').'/rsync.txt')) {
            $parameters .= sprintf(' --files-from=%s/rsync.txt', $input->getOption('rsync-dir'));
          }
        }
        
        $dryRun = $input->getOption('go') ? '' : '--dry-run';
        $command = "rsync $dryRun $parameters -e $ssh ./ $user$host:$dir";
        
        $process = new Process($command);
        $process->run();
    }
}