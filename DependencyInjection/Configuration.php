<?php

namespace Spraed\PDFGeneratorBundle\DependencyInjection;

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
     * Proxy to get root node for Symfony < 4.2.
     *
     * @param TreeBuilder $treeBuilder
     * @param string      $name
     *
     * @return NodeDefinition
     */
    protected function getRootNode(TreeBuilder $treeBuilder, string $name)
    {
        if (\method_exists($treeBuilder, 'getRootNode')) {
 
            return $treeBuilder->getRootNode();
        }
        
        return $treeBuilder->root($name);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('spraed_pdf_generator');

        $this->getRootNode($treeBuilder, 'spraed_pdf_generator')
            ->children()
            ->arrayNode('java')
            ->children()
            ->scalarNode('full_pathname')->defaultValue('')->end()
            ->end()
            ->end() // java
            ->end();

        return $treeBuilder;
    }
}
