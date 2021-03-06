<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Instantiator\Chainable;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\Instantiator\ChainableInstantiatorInterface;

class ProphecyChainableInstantiator extends AbstractChainableInstantiator
{
    /**
     * @var ChainableInstantiatorInterface
     */
    private $instantiator;

    public function __construct(AbstractChainableInstantiator $instantiator)
    {
        $this->instantiator = $instantiator;
    }

    /**
     * @inheritdoc
     */
    protected function createInstance(FixtureInterface $fixture)
    {
        return $this->instantiator->createInstance($fixture);
    }

    /**
     * @inheritdoc
     */
    public function canInstantiate(FixtureInterface $fixture): bool
    {
        return $this->instantiator->canInstantiate($fixture);
    }
}
