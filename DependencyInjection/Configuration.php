<?php

/*
 * (c) Simone Fumagalli <simone @ iliveinperego.com> - http://www.iliveinperego.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hpatoio\DeployBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('deploy');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        
        $rootNode->isRequired()
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('rsync_options')
			->defaultValue('-azC --force --delete --progress -h --checksum')
                                ->info('Default options used by the rsync command. You can override this value by passing --rsync-options on the command line.')
                                ->example('-azC --force --delete --progress -h --checksum')
                                ->end()
                        ->scalarNode('host')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('Name or IP of the remote server')
                                ->end()
                        ->scalarNode('dir')
                                ->defaultValue('')
                                ->info('Remote root for your project. NB: this is not the document root. Usually a level before.')
                                ->end()
                        ->scalarNode('user')
                                ->defaultValue('')
                                ->info('The user on the destination server. If none is specified your local user is used.')
                                ->end()
                        ->scalarNode('port')
                                ->defaultValue('22')
                                ->info('TCP port.')
                                ->end()
                        ->scalarNode('timeout')
                                ->defaultValue('60')
                                ->info('Process timeout in seconds. Set it to 0 for no timeout.')
                                ->end()
                        ->variableNode('post_deploy_operations')
                                ->info('Shell commands to run after deploy on the remote machine.')
                                ->end();
        
        return $treeBuilder;
    }
}
