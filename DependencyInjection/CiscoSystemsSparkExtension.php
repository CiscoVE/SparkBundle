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
        // Configuration
        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration( $configuration, $configs );
        
        $container->setParameter( 'cisco_systems_spark.client_id', $processedConfig[ 'client_id' ]);
        
        // Services
        $loader = new YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ));
        $loader->load( 'services.yml' );
    }
}
