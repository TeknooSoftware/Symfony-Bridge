<?php

/*
 * Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\Container;

use DI\Container as DIContainer;
use DI\ContainerBuilder as DIContainerBuilder;
use DI\Definition\ArrayDefinition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Reference as DIReference;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Symfony\Component\DependencyInjection\Definition as SfDefinition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference as SfReference;
use Teknoo\DI\SymfonyBridge\Container\Bridge;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilder;
use Teknoo\DI\SymfonyBridge\Container\Container;

/**
 * @covers \Teknoo\DI\SymfonyBridge\Container\BridgeBuilder
 * @covers \Teknoo\DI\SymfonyBridge\Container\BridgeTrait
 */
class BridgeBuilderTest extends TestCase
{
    private ?DIContainerBuilder $diBuilder = null;

    private ?SfContainerBuilder $sfContainer = null;

    /**
     * @return DIContainerBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getDiBuilderMock(): DIContainerBuilder
    {
        if (!$this->diBuilder instanceof DIContainerBuilder) {
            $this->diBuilder = $this->createMock(DIContainerBuilder::class);
        }

        return $this->diBuilder;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|SfContainerBuilder
     */
    private function getSfContainerBuilderMock(): SfContainerBuilder
    {
        if (!$this->sfContainer instanceof SfContainerBuilder) {
            $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        }

        return $this->sfContainer;
    }

    public function buildInstance(): BridgeBuilder
    {
        return new BridgeBuilder(
            $this->getDiBuilderMock(),
            $this->getSfContainerBuilderMock()
        );
    }

    public function testLoadDefinitionWithBadArgument()
    {
        $this->expectException(\TypeError::class);

        $this->buildInstance()->loadDefinition(new \stdClass());
    }

    public function testLoadDefinition()
    {
        self::assertInstanceOf(
            BridgeBuilder::class,
            $this->buildInstance()->loadDefinition(['foo', 'bar'])
        );
    }

    public function testImportWithBadArgument()
    {
        $this->expectException(\TypeError::class);

        $this->buildInstance()->import(new \stdClass(), new \stdClass());
    }

    public function testImport()
    {
        self::assertInstanceOf(
            BridgeBuilder::class,
            $this->buildInstance()->import('foo', 'bar')
        );
    }

    public function testInitializeSymfonyContainerWithDefaultDIContainer()
    {
        $this->expectException(\RuntimeException::class);

        $container = $this->createMock(DIContainer::class);

        $this->getDiBuilderMock()
            ->expects(self::any())
            ->method('build')
            ->willReturn($container);

        $this->buildInstance()->initializeSymfonyContainer();
    }

    public function testInitializeSymfonyContainerWithNotFoundEntry()
    {
        $definitionsFiles = [
            'foo',
            'bar'
        ];

        $container = $this->createMock(Container::class);
        $container->expects(self::any())
            ->method('getKnownEntryNames')
            ->willReturn([
                'entryNotFound',
            ]);

        $container->expects(self::any())
            ->method('extractDefinition')
            ->willReturn(null);

        $this->getDiBuilderMock()
            ->expects(self::any())
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderMock()
            ->expects(self::never())
            ->method('addDefinitions');

        $this->expectException(ServiceNotFoundException::class);

        self::assertInstanceOf(
            BridgeBuilder::class,
            $this->buildInstance()
                ->loadDefinition($definitionsFiles)
                ->import('hello', 'world')
                ->initializeSymfonyContainer()
        );
    }


    public function testInitializeSymfonyContainerWithNotSupportedCallableFactory()
    {
        $definitionsFiles = [
            'foo',
            'bar'
        ];

        $container = $this->createMock(Container::class);
        $container->expects(self::any())
            ->method('getKnownEntryNames')
            ->willReturn([
                'entryNotSupportedFactory',
            ]);

        $container->expects(self::any())
            ->method('extractDefinition')
            ->willReturn(
                (new FactoryDefinition(
                    'entryAboutFactoryInvokable',
                    'foo'
                ))
            );

        $this->getDiBuilderMock()
            ->expects(self::any())
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderMock()
            ->expects(self::never())
            ->method('addDefinitions');

        $this->expectException(\RuntimeException::class);

        self::assertInstanceOf(
            BridgeBuilder::class,
            $this->buildInstance()
                ->loadDefinition($definitionsFiles)
                ->import('hello', 'world')
                ->initializeSymfonyContainer()
        );
    }

    public function testInitializeSymfonyContainer()
    {
        $definitionsFiles = [
            'foo',
            'bar'
        ];

        $container = $this->createMock(Container::class);
        $container->expects(self::any())
            ->method('getKnownEntryNames')
            ->willReturn([
                \DateTimeInterface::class,
                \DateTime::class,
                'aliasInPHPDI',
                'aliasInSymfony',
                'entryAboutObject',
                'entryAboutFactoryClosure',
                'entryAboutFactoryInvokable',
                'entryAboutFactoryMethod',
                'entryAboutEnvironment',
                'entryAboutString',
                'entryAboutValue',
                'entryAboutArray',
            ]);

        $container->expects(self::exactly(12))
            ->method('extractDefinition')
            ->willReturnMap([
                [\DateTime::class, (new ObjectDefinition(\DateTime::class, \DateTime::class))],
                ['aliasInPHPDI', (new DIReference(\DateTime::class))],
                ['aliasInSymfony', (new DIReference('symfonyService'))],
                ['entryAboutObject', (new ObjectDefinition('entryAboutObject', \stdClass::class))],
                ['entryAboutFactoryClosure', (new FactoryDefinition('entryAboutFactoryClosure', function (): \stdClass {}))],
                [
                    'entryAboutFactoryInvokable',
                    (new FactoryDefinition(
                        'entryAboutFactoryInvokable',
                        new class {
                            public function __invoke(): \stdClass { }
                        }
                    ))
                ],
                [
                    'entryAboutFactoryMethod',
                    (new FactoryDefinition(
                        'entryAboutFactoryMethod',
                        [
                            new class {
                                public function method(): \stdClass { }
                            },
                            'method'
                        ]
                    ))
                ],
                [
                    'entryAboutEnvironment',
                    (new EnvironmentVariableDefinition('ENV_NAME'))
                ],
                [
                    'entryAboutString',
                    (new StringDefinition('stringValue'))
                ],
                [
                    'entryAboutValue',
                    (new ValueDefinition('value'))
                ],
                [
                    'entryAboutArray',
                    (new ArrayDefinition(['value']))
                ],
            ]);

        $this->getDiBuilderMock()
            ->expects(self::any())
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderMock()
            ->expects(self::once())
            ->method('setAlias')
            ->with('aliasInSymfony', 'symfonyService')
            ->willReturn($this->createMock(Alias::class));

        $this->getSfContainerBuilderMock()
            ->expects(self::exactly(4))
            ->method('setParameter')
            ->withConsecutive(
                [
                    'entryAboutEnvironment',
                    '%env(ENV_NAME)%',
                ],
                [
                    'entryAboutString',
                    'stringValue',
                ],
                [
                    'entryAboutValue',
                    'value',
                ],
                [
                    'entryAboutArray',
                    ['value'],
                ],
            );

        $this->getSfContainerBuilderMock()
            ->expects(self::once())
            ->method('addDefinitions')
            ->with(
                [
                    DIContainerBuilder::class => new SfDefinition(DIContainerBuilder::class),
                    Bridge::class =>  new SfDefinition(
                        Bridge::class,
                        [
                            new SfReference(DIContainerBuilder::class),
                            new SfReference('service_container'),
                            $definitionsFiles,
                            ['hello' => 'world']
                        ]
                    ),
                    \DateTimeInterface::class => (new SfDefinition(\DateTimeInterface::class))
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments([\DateTimeInterface::class])
                        ->setPublic(true),
                    \DateTime::class => (new SfDefinition(\DateTime::class))
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments([\DateTime::class])
                        ->setPublic(true),
                    'aliasInPHPDI' => (new SfDefinition(\DateTime::class))
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['aliasInPHPDI'])
                        ->setPublic(true),
                    'entryAboutObject' => (new SfDefinition(\stdClass::class))
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryAboutObject'])
                        ->setPublic(true),
                    'entryAboutFactoryClosure' => (new SfDefinition(\stdClass::class))
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryAboutFactoryClosure'])
                        ->setPublic(true),
                    'entryAboutFactoryInvokable' => (new SfDefinition(\stdClass::class))
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryAboutFactoryInvokable'])
                        ->setPublic(true),
                    'entryAboutFactoryMethod' => (new SfDefinition(\stdClass::class))
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryAboutFactoryMethod'])
                        ->setPublic(true),
                ]
            );

        self::assertInstanceOf(
            BridgeBuilder::class,
            $this->buildInstance()
                ->loadDefinition($definitionsFiles)
                ->import('hello', 'world')
                ->initializeSymfonyContainer()
        );
    }
}