<?php

namespace Contactlab\DeployBundle\DependencyInjection;

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
            }else{
                throw new \InvalidArgumentException('You must provide the host (e.g. http://example.com)');
            }
            if (isset($env_conf['dir'])) {
                $container->setParameter('deploy.'.$env.'.dir', $env_conf['dir']);
            }
            else {
                throw new \InvalidArgumentException('You must provide the dir (e.g. /var/www/project)');
            }
            
            $parameters = (isset($env_conf['parameters'])) ? $env_conf['parameters'] : array();
            
            $container->setParameter('deploy.'.$env.'.user', ($env_conf['user'])? $env_conf['user'].'@': '');
            $container->setParameter('deploy.'.$env.'.port', $env_conf['port']);
            $container->setParameter('deploy.'.$env.'.parameters', $parameters);
        }
        
    }

}	