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
    	
    	$node->children()
    	     ->scalarNode( 'granttype' )->isRequired()->cannotBeEmpty()->end()
    	     ->scalarNode( 'bottoken')->end()
    		 ->scalarNode( 'machine_id' )->end()
    	     ->scalarNode( 'machine_secret' )->end()
    	     ->scalarNode( 'machine_org' )->end()
    	     ->scalarNode( 'client_id' )->isRequired()->end()
    	     ->scalarNode( 'client_secret' )->end()
    	     ->scalarNode( 'redirect_url' )->end();
    	
    	return $treeBuilder;
    }
}

