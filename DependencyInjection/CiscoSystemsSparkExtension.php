<?php

namespace CiscoSystems\SparkBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;



class CiscoSystemsSparkExtension extends Extension
{
    public function load( array $configs, ContainerBuilder $container )
    {
        
        // Services
        $loader = new YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ));
        $loader->load( 'services.yml' );
        
        $config = array();
        foreach ($configs as $subConfig) {
        	$config = array_merge($config, $subConfig);
        }
        
        $container->setParameter( 'cisco.spark.config', $config );
        $container->setParameter( 'cisco.spark.client_id', $config['client_id'] );
        
    }
    
  
}
