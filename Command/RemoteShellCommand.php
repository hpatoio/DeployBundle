<?php

/*
 * (c) Maurizio Brioschi <maurizio.brioschi@ridesoft.org> - http://www.ridesoft.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hpatoio\DeployBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Process\Process;

class RemoteShellCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('project:remoteshellcmd')
            ->setDescription('Execute a command on the remote root')
            ->addArgument('env', InputArgument::REQUIRED, 'The environment where you want to execute the command')
            ->addArgument('cmd', null, InputArgument::REQUIRED, 'The command')
            ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $config_root_path = $this->getContainer()->get('kernel')->getRootDir()."/config/";
        $output->getFormatter()->setStyle('notice', new OutputFormatterStyle('red', 'yellow'));
        $available_env = $this->getContainer()->getParameter('deploy.config');       
        
        $env = $input->getArgument('env');
        $cmd = $input->getArgument('cmd');
        if (!in_array($env, array_keys($available_env))) {
            $output->writeln('<notice>Env value not valid.</notice>');
            exit();
        }
        
        foreach ($available_env[$env] as $key => $value) {
            $$key = $value;
        }
         
        $ssh = "ssh -t -t -p".$port." $user@$host \"cd $dir;$cmd;exit;\"";
  
        $output->writeln(sprintf("Running cmd on remote server in environment\n", $env));

        $command = $ssh;
        $output->writeln($command);
        $process = new Process($command);
        $process->run(function ($type, $buffer) use ($output) {                
                $output->write($buffer);
            });        
        $output->writeln("\nDone");
        
        $output->writeln("");
        
    }
}

