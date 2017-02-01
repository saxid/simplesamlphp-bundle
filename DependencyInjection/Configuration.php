<?php

namespace Saxid\SimplesamlphpBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('saxid_simplesamlphp');

        $rootNode
            ->children()
                ->scalarNode('sp')->defaultValue('default-sp')->end()
                //SAML 2 commonName
                //->scalarNode('authentication_attribute')->defaultValue('urn:oid:2.5.4.3')->end()
                ->arrayNode('authentication_attribute')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('urn:oid:1.3.6.1.4.1.5923.1.1.1.6', 'urn:mace:dir:attribute-def:eduPersonPrincipalName'))
                        ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
