<?php

/*
 * DI Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\DI\SymfonyBridge\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Add new available keys into Symfony configuration, to configure this bundle, ideally to be defined in
 * /config/package/di_bridge.yaml :
 *     di_bridge:
 *          #Optional, To enable PHP-DI's container compilation (disable by default)
 *          compilation_path: ~ #Default, or path to store cache, like '%kernel.project_dir%/var/cache/phpdi'
 *          #Optional, To enable PHP-DI's cache (disable by default)
 *          enable_cache: false #Default or true
 *          definitions:
 *              - 'list of PHP-DI definitions file, you can use Symfony joker like %kernel.project_dir%'
 *              #example
 *              - '%kernel.project_dir%/vendor/editor_name/package_name/src/di.php'
 *              - '%kernel.project_dir%/config/di.php'
 *          import: #Optional
 *              #To make alias from SF entries into PHPDI
 *              My\Class\Name: 'symfony.contaner.entry.name'
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('di_bridge');

        $root = $treeBuilder->getRootNode();
        $root->children()
            ->scalarNode('compilation_path')
                ->defaultNull()
            ->end()//compilation_path
            ->booleanNode('enable_cache')
                ->defaultFalse()
            ->end() //enable_cache
            ->arrayNode('definitions')
                ->scalarPrototype()->end()
            ->end() // definitions
            ->arrayNode('import')
                ->useAttributeAsKey('name')
                ->scalarPrototype()->end()
            ->end() //import
        ->end();

        return $treeBuilder;
    }
}
