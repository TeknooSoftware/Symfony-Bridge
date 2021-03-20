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

namespace Teknoo\DI\SymfonyBridge\Container;

use DI\Container as DIContainer;
use DI\ContainerBuilder as DIContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container as SfContainer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Bridge implements ContainerInterface
{
    use BridgeTrait;

    private DIContainerBuilder $diBuilder;

    private SfContainer $sfContainer;

    /**
     * @var array<int, string>
     */
    private array $definitionsFiles;

    /**
     * @var array<string, string>
     */
    private array $definitionsImport;

    private ?DIContainer $diContainer = null;

    private ?string $compilationPath = null;

    private bool $cacheEnabled = false;

    /**
     * @param array<int, string> $definitionsFiles
     * @param array<string, string> $definitionsImport
     */
    public function __construct(
        DIContainerBuilder $diBuilder,
        SfContainer $sfContainer,
        array $definitionsFiles,
        array $definitionsImport,
        ?string $compilationPath = null,
        bool $enableCache = false
    ) {
        $this->diBuilder = $diBuilder;
        $this->sfContainer = $sfContainer;
        $this->definitionsFiles = $definitionsFiles;
        $this->definitionsImport = $definitionsImport;
        $this->compilationPath = $compilationPath;
        $this->cacheEnabled = $enableCache;
    }

    private function getDIContainer(): DIContainer
    {
        if (null !== $this->diContainer) {
            return $this->diContainer;
        }

        return $this->diContainer = $this->buildContainer(
            $this->diBuilder,
            $this,
            $this->definitionsFiles,
            $this->definitionsImport,
            $this->compilationPath,
            $this->cacheEnabled
        );
    }

    /**
     * Service Factory used by Symfony' container to get service instance from PHP-DI
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __invoke(string $id): mixed
    {
        $diContainer = $this->getDIContainer();

        if ($diContainer->has($id)) {
            $object = $diContainer->get($id);

            if ($object instanceof ContainerAwareInterface) {
                $object->setContainer($this->sfContainer);
            }

            return $object;
        }

        throw new ServiceNotFoundException($id);
    }

    /**
     * Get Service bridge used from PHP-DI's Definition to manage parameters access : In SF, Parameters are accessible
     * only via getParameter, but in PHP-DI via the method "get" like other definition.
     *
     * @param string $id
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function get($id): mixed
    {
        if ($this->sfContainer->has($id)) {
            return $this->sfContainer->get($id);
        }

        $diContainer = $this->getDIContainer();
        if ($diContainer->has($id)) {
            return $diContainer->get($id);
        }

        if ($this->sfContainer->hasParameter($id)) {
            return $this->sfContainer->getParameter($id);
        }

        throw new ServiceNotFoundException($id);
    }

    /**
     * Service checking bridge used from PHP-DI's Definition to manage parameters access : In SF, Parameters are
     * accessible only via getParameter, but in PHP-DI via the method "get" like other definition.
     *
     * @param string $id
     */
    public function has($id): bool
    {
        if ($this->sfContainer->has($id)) {
            return true;
        }

        $diContainer = $this->getDIContainer();
        return $diContainer->has($id) || $this->sfContainer->hasParameter($id);
    }
}
