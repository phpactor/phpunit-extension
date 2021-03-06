<?php

namespace Phpactor\Extension\PHPUnit;

use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\Container\Extension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\PHPUnit\CodeTransform\TestGenerator;
use Phpactor\Extension\PHPUnit\FrameWalker\AssertInstanceOfWalker;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

class PHPUnitExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $this->registerWorseReflection($container);
        $this->registerCodeTransform($container);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
    }

    private function registerWorseReflection(ContainerBuilder $container)
    {
        $container->register('phpunit.frame_walker.assert_instance_of', function (Container $container) {
            return new AssertInstanceOfWalker();
        }, [ WorseReflectionExtension::TAG_FRAME_WALKER => [] ]);
    }

    private function registerCodeTransform(ContainerBuilder $container)
    {
        $container->register('phpunit.code_transform.test_generator', function (Container $container) {
            return new TestGenerator();
        }, [CodeTransformExtension::TAG_NEW_CLASS_GENERATOR => ['name' => 'phpunit']]);
    }
}
