<?php

/*
 * Symfony Bridge.
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

namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Teknoo\DI\SymfonyBridge\DependencyInjection\Configuration;

/**
 * @covers \Teknoo\DI\SymfonyBridge\DependencyInjection\Configuration
 */
class ConfigurationTest extends TestCase
{
    public function buildInstance(): Configuration
    {
        return new Configuration();
    }

    public function testGetConfigTreeBuilder()
    {
        $treeBuilder = $this->buildInstance()->getConfigTreeBuilder();

        self::assertInstanceOf(
            TreeBuilder::class,
            $treeBuilder
        );
    }
}
