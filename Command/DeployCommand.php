<?php

/*
 * (c) Simone Fumagalli <simone @ iliveinperego.com> - http://www.iliveinperego.com/
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
            ->addOption('rsync-options', null, InputOption::VALUE_NONE, 'Options to pass to the rsync executable')
            ->addOption('force-vendor', null, InputOption::VALUE_NONE, 'Force sync of vendor dir.')
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
        
        if (!in_array($env, array_keys($available_env))) {
            throw new \InvalidArgumentException(sprintf('\'%s\' is no a valid environment. Valid environments: %s', $env, implode(",",array_keys($available_env))));
        }
        
        foreach ($available_env[$env] as $key => $value) {
            $$key = $value;
        }
        
        $ssh = 'ssh -p '.$port.'';
        
        if ($input->getOption('rsync-options'))
            $rsync_options = $input->getOption('rsync-options');

        if ($input->getOption('force-vendor'))
            $rsync_options .= " --include 'vendor' ";

        $exclude_file_found = false;
        
        if (file_exists($config_root_path.'rsync_exclude.txt')) {
            $rsync_options .= sprintf(' --exclude-from="%srsync_exclude.txt"', $config_root_path);
            $exclude_file_found = true;
        }
        
        if (file_exists($config_root_path."rsync_exclude_{$env}.txt")) {
            $rsync_options .= sprintf(" --exclude-from=\"%srsync_exclude_{$env}.txt\"", $config_root_path);
            $exclude_file_found = true;
        }

        if (!$exclude_file_found) {
            $output->writeln(sprintf('<notice>No rsync_exclude file found, nothing excluded.</notice> If you want an rsync_exclude.txt template get it here http://bit.ly/rsehdbsf2', $config_root_path."rsync_exclude.txt"));
            $output->writeln("");
        }

        $dryRun = $input->getOption('go') ? '' : '--dry-run';
        
        $user = ($user !='') ? $user."@" : "";

        $command = "rsync $dryRun $rsync_options -e \"$ssh\" ./ $user$host:$dir";

        $output->writeln(sprintf('%s on <info>%s</info> server with <info>%s</info> command',
            ($dryRun) ? 'Fake deploying' : 'Deploying',
            $input->getArgument('env'),
            $command));

        $process = new Process($command);
        $process->setTimeout(($timeout == 0) ? null : $timeout);

        $output->writeln("\nSTART deploy\n--------------------------------------------");

        $process->run(function ($type, $buffer) use ($output) {
                        if ('err' === $type) {
                            $output->write( 'ERR > '.$buffer);
                        } else {
                            $output->write($buffer);
                        }
                    });

        $output->writeln("\nEND deploy\n--------------------------------------------\n");

        if ($dryRun) {

            $output->writeln('<notice>This was a simulation, --go was not specified. Post deploy operation not run.</notice>');
            $output->writeln(sprintf('<info>Run the command with --go for really copy the files to %s server.</info>', $env));

        } else {
            
            $output->writeln(sprintf("Deployed on <info>%s</info> server!\n", $env));

            if ( isset($post_deploy_operations) && count($post_deploy_operations) > 0 ) {
                
                $post_deploy_commands = implode("; ", $post_deploy_operations);

                $output->writeln(sprintf("Running post deploy commands on <info>%s</info> server!\n", $env));

                $command = "$ssh $user$host 'cd \"$dir\";".$post_deploy_commands."'";

                $process = new Process($command);
                $process->setTimeout(($timeout == 0) ? null : $timeout);
                $process->run(function ($type, $buffer) use ($output) {
                        if ('err' === $type) {
                            $output->write( 'ERR > '.$buffer);
                        } else {
                            $output->write($buffer);
                        }
                    });

                $output->writeln("\nDone");

            } 

        }

        $output->writeln("");

    }
}
