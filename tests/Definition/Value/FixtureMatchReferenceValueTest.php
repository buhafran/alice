<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;

/**
 * @covers Nelmio\Alice\Definition\Value\FixtureMatchReferenceValue
 */
class FixtureMatchReferenceValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(FixtureMatchReferenceValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $regex = '/dummy/';
        $value = new FixtureMatchReferenceValue($regex);

        $this->assertEquals($regex, $value->getValue());
    }

    public function testCanMatchAgainstValues()
    {
        $regex = '/^d/';
        $value = new FixtureMatchReferenceValue($regex);

        $this->assertTrue($value->match('d'));
        $this->assertFalse($value->match('a'));
    }

    public function testCanCreateAReferenceForWildcards()
    {
        $expected = new FixtureMatchReferenceValue('/^user.*/');
        $actual = FixtureMatchReferenceValue::createWildcardReference('user');

        $this->assertEquals($expected, $actual);
    }
}
