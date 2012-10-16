<?php

/*
 * (c) Simone Fumagalli <simone @ iliveinperego.com> - http://www.iliveinperego.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hpatoio\DeployBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DeployExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {

        $deploy_config = array_pop($configs);

        foreach ($deploy_config as $env => $env_conf) {

            if (isset($env_conf['host'])) {
                $container->setParameter('deploy.'.$env.'.host', trim($env_conf['host'], '/'));
            } else {
                throw new \InvalidArgumentException('You must provide the host (e.g. http://example.com)');
            }
            if (isset($env_conf['dir'])) {
                $container->setParameter('deploy.'.$env.'.dir', $env_conf['dir']);
            } else {
                throw new \InvalidArgumentException('You must provide the dir (e.g. /var/www/project)');
            }

            $rsync_options = (isset($env_conf['rsync-options'])) ? $env_conf['rsync-options'] : null;

            $container->setParameter('deploy.'.$env.'.user', ($env_conf['user'])? $env_conf['user'].'@': '');
            $container->setParameter('deploy.'.$env.'.port', $env_conf['port']);
            $container->setParameter('deploy.'.$env.'.rsync-options', $rsync_options);
        }

    }

}
