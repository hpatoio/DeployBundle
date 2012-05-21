<?php

namespace Hpatoio\DeployBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DeployCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
        	->setName('project:deploy')
        	->setDescription('Deploy your project via rsync')
        	->addArgument('env', InputArgument::REQUIRED, 'The environment where you want to deploy the project')
            ->addOption('go', null, InputOption::VALUE_NONE, 'Do the deployment')
            ->addOption('rsync-options', null, InputOption::VALUE_OPTIONAL, 'To options to pass to the rsync executable', '-azC --force --delete --progress -h')
	        ;
    }
    
    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env    = $input->getArgument('env');
        
        if(!$this->getContainer()->getParameter('deploy.'.$env.'.host')){
            throw new \InvalidArgumentException('You must provide the host (e.g. http://example.com)');
        }
        
        $host   = $this->getContainer()->getParameter('deploy.'.$env.'.host');
        $dir    = $this->getContainer()->getParameter('deploy.'.$env.'.dir');
        $user   = $this->getContainer()->getParameter('deploy.'.$env.'.user');
        $port   = $this->getContainer()->getParameter('deploy.'.$env.'.port');
        
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }
        
        $ssh = 'ssh';

        if ($port) {
          $ssh = '"ssh -p'.$port.'"';
        }
        
		$parameters = $input->getOption('rsync-options') ? $input->getOption('rsync-options') : '-azC --force --delete --progress -h';
		
		$config_root_path = $this->getContainer()->get('kernel')->getRootDir()."/config/";
		
		if (file_exists($config_root_path.'rsync_exclude.txt')) {
			$parameters .= sprintf(' --exclude-from=%srsync_exclude.txt', $config_root_path);
		}
		
        $dryRun = $input->getOption('go') ? '' : '--dry-run';
        
        $command = "rsync $dryRun $parameters -e $ssh ./ $user$host:$dir";
        
        $output->writeln(sprintf('%s on <info>%s</info> server with <info>%s</info> command', 
            ($dryRun) ? 'Fake deploying' : 'Deploying',
            $env, 
            $command));
            
        $process = new Process($command);
        $process->run();
        
        $output->writeln("\nSTART deploy\n--------------------------------------------");
        
        $output->writeln($process->getOutput());
        
        $output->writeln("--------------------------------------------\n");
        
        $output->writeln(sprintf('Deployed on <info>%s</info> server!', $env));
    }
}
