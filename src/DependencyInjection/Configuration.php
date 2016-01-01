<?php

namespace CiscoSystems\SparkBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root( 'cisco_systems_spark' );
        $node
            ->children()
                
                ->arrayNode( 'machine_id' )
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode( 'class' )->defaultNull()->end()
                        ->scalarNode( 'property' )->defaultValue( 'id' )->end()
                    ->end()
                ->end()
                ->arrayNode( 'machine_secret' )
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode( 'class' )->defaultNull()->end()
                        ->scalarNode( 'property' )->defaultValue( 'id' )->end()
                    ->end()
                ->end()
                ->arrayNode( 'client_id' )
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode( 'class' )->defaultNull()->end()
                        ->scalarNode( 'property' )->defaultValue( 'id' )->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}

