<?php

namespace CiscoSystems\SparkBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

class CiscoSystemsSparkBundle extends Bundle
{
    protected $kernel;

    public function __construct( KernelInterface $kernel )
    {
        $this->kernel = $kernel;
    }

}
