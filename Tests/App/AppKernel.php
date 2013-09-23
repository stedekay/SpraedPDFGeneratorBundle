<?php

namespace Spraed\PDFGeneratorBundle\Tests\App;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // dependencies
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),

            // my bundle to test
            new Spraed\PDFGeneratorBundle\SpraedPDFGeneratorBundle(),
        );

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // We don't need that environment stuff, just one config
        $loader->load(__DIR__.'/Resources/config.yml');
    }
}