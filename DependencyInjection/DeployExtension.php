<?php

namespace Bundle\DeployBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DeployExtension extends Extension
{

    public function configLoad($config, ContainerBuilder $container)
    {
        foreach ($config as $server => $config) {
            if (isset($config['host'])) {
                $container->setParameter('deploy.'.$server.'.host', trim($config['host'], '/'));
            }else{
                throw new \InvalidArgumentException('You must provide the host (e.g. http://example.com)');
            }
            if (isset($config['dir'])) {
                $container->setParameter('deploy.'.$server.'.dir', $config['dir']);
            }
            else {
                throw new \InvalidArgumentException('You must provide the dir (e.g. /var/www/project)');
            }
            
            $parameters = (isset($config['parameters'])) ? $config['parameters'] : array();

            $container->setParameter('deploy.'.$server.'.user', ($config['user'])? $config['user'].'@': '');
            $container->setParameter('deploy.'.$server.'.port', $config['port']);
            $container->setParameter('deploy.'.$server.'.parameters', $parameters);
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return null;
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/symfony';
    }

    public function getAlias()
    {
        return 'deploy';
    }

}