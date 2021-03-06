<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Fixture;

use Nelmio\Alice\Definition\FakeMethodCall;
use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\FixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\MutableFixture;
use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Exception\FixtureNotFoundException;
use Nelmio\Alice\FixtureBag;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Fixture\TemplatingFixtureBag
 */
class TemplatingFixtureBagTest extends \PHPUnit_Framework_TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $fixtureId = 'user0';
        $fixture = new DummyFixture($fixtureId);

        $templateId = 'user_base';
        $template = new TemplatingFixture(
            new FixtureWithFlags(
                new DummyFixture($templateId),
                (new FlagBag('user_base'))->with(new TemplateFlag())
            )
        );
        
        $bag = (new TemplatingFixtureBag())
            ->with($fixture)
            ->with($template)
        ;

        $this->assertTrue($bag->has($fixtureId));
        $this->assertEquals($fixture, $bag->get($fixtureId));

        $this->assertTrue($bag->has($templateId));
        $this->assertEquals($template, $bag->get($templateId));

        $this->assertFalse($bag->has('foo'));
        try {
            $bag->get('foo');
        } catch (FixtureNotFoundException $exception) {
            $this->assertEquals(
                'Could not find the fixture "foo".',
                $exception->getMessage()
            );
        }

        $this->assertEquals(
            (new FixtureBag())->with($fixture),
            $bag->getFixtures()
        );
    }

    /**
     * @depends Nelmio\Alice\FixtureBagTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $fixture = new MutableFixture('user0', 'Nelmio\Alice\Entity\User', SpecificationBagFactory::create());
        $originalFixture = deep_clone($fixture);

        $bag = (new TemplatingFixtureBag())->with($fixture);

        // Mutate injected value
        $fixture->setSpecs(SpecificationBagFactory::create(new FakeMethodCall()));

        // Mutate retrieved fixture
        $bag->getFixtures()->get('user0')->setSpecs(SpecificationBagFactory::create(new NoMethodCall()));

        $this->assertEquals($originalFixture, $bag->getFixtures()->get('user0'));
    }

    public function testAddTemplateFixtureToTemplates()
    {
        $fixture = new DummyFixture('user0');
        $template = new TemplatingFixture(
            new FixtureWithFlags(
                new DummyFixture('user_base'),
                (new FlagBag('user_base'))->with(new TemplateFlag())
            )
        );

        $bag = (new TemplatingFixtureBag())
            ->with($fixture)
            ->with($template)
        ;

        $this->assertEquals(
            (new FixtureBag())
                ->with($fixture)
            ,
            $bag->getFixtures()
        );
    }
}
