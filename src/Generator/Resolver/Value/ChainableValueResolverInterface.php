<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;

interface ChainableValueResolverInterface extends ValueResolverInterface
{
    public function canResolve(ValueInterface $value): bool;
}
