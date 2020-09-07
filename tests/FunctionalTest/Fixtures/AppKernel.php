<?php

namespace DI\Bridge\Symfony\Test\FunctionalTest\Fixtures;

use DI\ContainerBuilder;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    private $configFile;

    public function __construct($configFile)
    {
        $this->configFile = $configFile;

        parent::__construct('dev', true);
    }

    protected function buildPHPDIContainer(ContainerBuilder $builder)
    {
        return $builder->build();
    }

    public function registerBundles()
    {
        return array();
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/' . $this->configFile);
    }

    protected function getContainerClass()
    {
        return $this->randomName();
    }

    private function randomName() {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $str = '';
        for ($i = 0; $i < 10; $i++) {
            $str .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $str;
    }
}
