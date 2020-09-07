<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Bridge\Symfony\Test\FunctionalTest;

use DI\Bridge\Symfony\Test\FunctionalTest\Fixtures\Class1;
use DI\Bridge\Symfony\Test\FunctionalTest\Fixtures\Class2;
use Psr\Container\ContainerInterface;

/**
 * @coversNothing
 */
class KernelTest extends AbstractFunctionalTest
{
    public function testKernelShouldBoot()
    {
        $kernel = $this->createKernel();

        self::assertInstanceOf(ContainerInterface::class, $kernel->getContainer());
    }

    public function testPhpdiShouldResolveClasses()
    {
        $kernel = $this->createKernel();

        $object = $kernel->getContainer()->get(Class1::class);
        self::assertInstanceOf(Class1::class, $object);
    }

    public function testSymfonyShouldResolveClasses()
    {
        $kernel = $this->createKernel('class2.yml');

        $object = $kernel->getContainer()->get('class2');
        self::assertInstanceOf(Class2::class, $object);
    }
}
