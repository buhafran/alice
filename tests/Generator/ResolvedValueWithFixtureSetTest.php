<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

/**
 * @covers Nelmio\Alice\Generator\ResolvedValueWithFixtureSet
 */
class ResolvedValueWithFixtureSetTest extends \PHPUnit_Framework_TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $value = new \stdClass();
        $set = ResolvedFixtureSetFactory::create();

        $resolvedValueWithSet = new ResolvedValueWithFixtureSet($value, $set);

        $this->assertEquals($value, $resolvedValueWithSet->getValue());
        $this->assertEquals($set, $resolvedValueWithSet->getSet());
    }

    /**
     * @depends Nelmio\Alice\Generator\ResolvedFixtureSetTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $value = new \stdClass();
        $set = ResolvedFixtureSetFactory::create();

        $resolvedValueWithSet = new ResolvedValueWithFixtureSet($value, $set);

        // Mutate injected value
        $value->foo = 'bar';

        // Mutate retrieved value
        $resolvedValueWithSet->getValue()->foo = 'baz';

        $this->assertEquals(new \stdClass(), $resolvedValueWithSet->getValue());
        $this->assertEquals($set, $resolvedValueWithSet->getSet());
    }
}
