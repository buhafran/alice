<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Entity\Hydrator\Dummy;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Symfony\PropertyAccess\FakePropertyAccessor;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Value\Chainable\FixturePropertyReferenceResolver
 */
class FixturePropertyReferenceResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(FixturePropertyReferenceResolver::class, ChainableValueResolverInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new FixturePropertyReferenceResolver(new FakePropertyAccessor());
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $resolver = new FixturePropertyReferenceResolver(new FakePropertyAccessor());
        $newResolver = $resolver->withResolver(new FakeValueResolver());

        $this->assertEquals(new FixturePropertyReferenceResolver(new FakePropertyAccessor()), $resolver);
        $this->assertEquals(new FixturePropertyReferenceResolver(new FakePropertyAccessor(), new FakeValueResolver()), $newResolver);
    }

    public function testCanResolvePropertyReferenceValues()
    {
        $resolver = new FixturePropertyReferenceResolver(new FakePropertyAccessor());

        $this->assertTrue($resolver->canResolve(new FixturePropertyValue(new FakeValue(), '')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\FixturePropertyReferenceResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $value = new FixturePropertyValue(new FakeValue(), '');
        $resolver = new FixturePropertyReferenceResolver(new FakePropertyAccessor());
        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create());
    }

    public function testReturnsSetWithResolvedValue()
    {
        $value = new FixturePropertyValue(
            $reference = new FakeValue(),
            $property = 'prop'
        );
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));
        $scope = ['val' => 'scopie'];

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve($reference, $fixture, $set, $scope)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    $instance = new \stdClass(),
                    $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['ping' => 'pong']))
                )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue($instance, $property)->willReturn('yo');
        /** @var PropertyAccessorInterface $propertyAccessor */
        $propertyAccessor = $propertyAccessorProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet('yo', $newSet);

        $resolver = new FixturePropertyReferenceResolver($propertyAccessor, $valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope);

        $this->assertEquals($expected, $actual);

        $valueResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $propertyAccessorProphecy->getValue(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException
     */
    public function testCatchesAccessorExceptionsToThrowResolverException()
    {
        $value = new FixturePropertyValue(
            $reference = new FakeValue(),
            $property = 'prop'
        );
        $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(Argument::cetera())
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    $instance = new \stdClass(),
                    $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['ping' => 'pong']))
                )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue(Argument::cetera())->willThrow(\Exception::class);
        /** @var PropertyAccessorInterface $propertyAccessor */
        $propertyAccessor = $propertyAccessorProphecy->reveal();

        $resolver = new FixturePropertyReferenceResolver($propertyAccessor, $valueResolver);
        $resolver->resolve($value, new FakeFixture(), $set);
    }

    public function testTestResolutionWithSymfonyPropertyAccessor()
    {
        $value = new FixturePropertyValue(
            $reference = new FakeValue(),
            $property = 'publicProperty'
        );

        $instance = new Dummy();
        $instance->publicProperty = 'foo';

        $set = ResolvedFixtureSetFactory::create();

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(Argument::cetera())
            ->willReturn(
                new ResolvedValueWithFixtureSet($instance, $set)
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet('foo', $set);

        $resolver = new FixturePropertyReferenceResolver(PropertyAccess::createPropertyAccessor(), $valueResolver);
        $actual = $resolver->resolve($value, new FakeFixture(), $set);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException
     */
    public function testThrowsAnExceptionIfReferenceResolvedIsNotAnObject()
    {
        $value = new FixturePropertyValue(
            $reference = new FakeValue(),
            $property = 'publicProperty'
        );

        $instance = new Dummy();
        $instance->publicProperty = 'foo';

        $set = ResolvedFixtureSetFactory::create();

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(Argument::cetera())
            ->willReturn(
                new ResolvedValueWithFixtureSet('string value', $set)
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $resolver = new FixturePropertyReferenceResolver(PropertyAccess::createPropertyAccessor(), $valueResolver);
        $resolver->resolve($value, new FakeFixture(), $set);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\NoSuchPropertyException
     */
    public function testThrowsAnExceptionIfResolvedReferenceHasNoSuchProperty()
    {
        $value = new FixturePropertyValue(
            $reference = new FakeValue(),
            $property = 'prop'
        );

        $instance = new \stdClass();
        $instance->prop = 'foo';

        $set = ResolvedFixtureSetFactory::create();

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(Argument::cetera())
            ->willReturn(
                new ResolvedValueWithFixtureSet(new \stdClass(), $set)
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $resolver = new FixturePropertyReferenceResolver(PropertyAccess::createPropertyAccessor(), $valueResolver);
        $resolver->resolve($value, new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create()), $set);
    }
}
