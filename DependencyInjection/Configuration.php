<?php

namespace CiscoSystems\SparkBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
    	$treeBuilder = new TreeBuilder();
    	$node = $treeBuilder->root( 'CiscoSpark' );
    	
    	$node->children()
    	     ->scalarNode( 'granttype' )->isRequired()->cannotBeEmpty()->end()
    		 ->scalarNode( 'machine_id' )->isRequired()->cannotBeEmpty()->end()
    	     ->scalarNode( 'machine_secret' )->isRequired()->cannotBeEmpty()->end()
    	     ->scalarNode( 'machine_org' )->end()
    	     ->scalarNode( 'client_id' )->isRequired()->cannotBeEmpty()->end()
    	     ->scalarNode( 'client_secret' )->isRequired()->cannotBeEmpty()->end();
    	
    	return $treeBuilder;
    }
}

