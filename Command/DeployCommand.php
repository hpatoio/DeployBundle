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
            ->addOption('cache-warmup', null, InputOption::VALUE_NONE, 'Run cache:warmup command on destination server')
            ->addOption('rsync-options', null, InputOption::VALUE_OPTIONAL, 'Options to pass to the rsync executable', '-azC --force --delete --progress -h')
            ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->getFormatter()->setStyle('notice', new OutputFormatterStyle('red', 'yellow'));

        $env    = $input->getArgument('env');

        if (!$this->getContainer()->getParameter('deploy.'.$env.'.host')) {
            throw new \InvalidArgumentException('You must provide the host');
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

    $rsync_options = $input->getOption('rsync-options');

    if ($this->getContainer()->getParameter('deploy.'.$env.'.rsync-options'))
            $rsync_options = $this->getContainer()->getParameter('deploy.'.$env.'.rsync-options');

    if ($input->getOption('rsync-options') !== '-azC --force --delete --progress -h')
        $rsync_options = $input->getOption('rsync-options');

        $config_root_path = $this->getContainer()->get('kernel')->getRootDir()."/config/";

        if (file_exists($config_root_path.'rsync_exclude.txt')) {
            $rsync_options .= sprintf(' --exclude-from=%srsync_exclude.txt', $config_root_path);
        }

        $dryRun = $input->getOption('go') ? '' : '--dry-run';

        $command = "rsync $dryRun $rsync_options -e $ssh ./ $user$host:$dir";

        $output->writeln(sprintf('%s on <info>%s</info> server with <info>%s</info> command',
            ($dryRun) ? 'Fake deploying' : 'Deploying',
            $env,
            $command));

        $process = new Process($command);

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

            $output->writeln('<notice>This was a simulation, --go was not specified.</notice>');
            $output->writeln(sprintf('<info>Run the command with --go for really copy the files to %s server.</info>', $env));

        } else {
            $output->writeln(sprintf("Deployed on <info>%s</info> server!\n", $env));

            if ( $input->getOption('cache-warmup') ) {

                $output->writeln(sprintf("Running cache:warmup on <info>%s</info> server!\n", $env));

                $command = "ssh $user$host 'cd $dir;php app/console cache:warmup -e $env'";

                $process = new Process($command);
                $process->run(function ($type, $buffer) use ($output) {
                        if ('err' === $type) {
                            $output->write( 'ERR > '.$buffer);
                        } else {
                            $output->write($buffer);
                        }
                    });

                $output->writeln("\nDone");

            } else {

                $output->writeln(sprintf("<notice>Cache was not regenerated on %s server so you might not see changes.</notice> Login to %s server and run:\n\n<info> app/console cache:warmup</info>", $env, $env));

            }

        }

        $output->writeln("");

    }
}
