<?php

namespace Paggy\BoletoBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('paggy_boleto');

        // Very similar to BoletoView::$viewFields, but different:
        // The $viewFields are the fields required by the Twig templates, no more, no less
        // The configuration is mostly used to populate them, but there is no strict one-to-one relationship
        $rootNode
            ->fixXmlConfig('cedant')
            ->fixXmlConfig('drawer')
            ->fixXmlConfig('payer')
            ->fixXmlConfig('payslip')
            ->fixXmlConfig('path')
            ->children()
                ->arrayNode('cedants')
                    ->useAttributeAsKey('alias')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('cpf')->end()
                            ->scalarNode('cnpj')->end()
                            ->enumNode('bank')
                                ->values(array('BB', 'CEF')) // only "CEF" supported at the moment
                            ->end()
                            ->scalarNode('branch')->end()
                            ->scalarNode('account')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('drawers')
                    ->useAttributeAsKey('alias')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('cpf')->end()
                            ->scalarNode('cnpj')->end()
                            ->scalarNode('address_line1')->end()
                            ->scalarNode('address_line2')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('payers')
                    ->useAttributeAsKey('alias')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('cpf')->end()
                            ->scalarNode('cnpj')->end()
                            ->scalarNode('address_line1')->end()
                            ->scalarNode('address_line2')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('payslips')
                    ->useAttributeAsKey('alias')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('title')->end()
                            ->scalarNode('value')->end()
                            ->scalarNode('due_date')->end()
                            ->scalarNode('currency')->end()
                            ->scalarNode('document_number')->end()
                            ->scalarNode('description')->end()
                            ->scalarNode('instructions')->end()
                            ->scalarNode('payment_location')->end()
                            ->scalarNode('wallet')->end()
                            ->scalarNode('acceptance')->end()
                            ->scalarNode('document_type')->end()
                            ->scalarNode('document_date')->end()
                            ->scalarNode('rendering_date')->end()
                            ->scalarNode('our_number')->end()
                            ->scalarNode('date_format')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('paths')
                    ->useAttributeAsKey('alias')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('favicon')->end()
                            ->scalarNode('logo_cedant_bank')->end()
                            ->scalarNode('logo_drawer')->end()
                            ->scalarNode('helper_class')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
