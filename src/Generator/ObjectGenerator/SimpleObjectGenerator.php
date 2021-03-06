<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\ObjectGenerator;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectBag;

final class SimpleObjectGenerator implements ObjectGeneratorInterface
{
    use NotClonableTrait;
    
    /**
     * @var InstantiatorInterface
     */
    private $instantiator;
    
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var CallerInterface
     */
    private $caller;

    public function __construct(
        ValueResolverInterface $valueResolver,
        InstantiatorInterface $instantiator,
        HydratorInterface $hydrator,
        CallerInterface $caller
    ) {
        if ($valueResolver instanceof ObjectGeneratorAwareInterface) {
            $valueResolver = $valueResolver->withGenerator($this);
        }

        if ($instantiator instanceof ValueResolverAwareInterface) {
            $instantiator = $instantiator->withResolver($valueResolver);
        }

        if ($hydrator instanceof ValueResolverAwareInterface) {
            $hydrator = $hydrator->withResolver($valueResolver);
        }

        if ($caller instanceof ValueResolverAwareInterface) {
            $caller = $caller->withResolver($valueResolver);
        }

        $this->instantiator = $instantiator;
        $this->hydrator = $hydrator;
        $this->caller = $caller;
    }

    /**
     * @inheritdoc
     */
    public function generate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet): ObjectBag
    {
        $fixtureSet = $this->instantiator->instantiate($fixture, $fixtureSet);
        $instantiatedObject = $fixtureSet->getObjects()->get($fixture);
        
        $fixtureSet = $this->hydrator->hydrate($instantiatedObject, $fixtureSet);
        $hydratedObject = $fixtureSet->getObjects()->get($fixture);
        
        $fixtureSet = $this->caller->doCallsOn($hydratedObject, $fixtureSet);
        
        return $fixtureSet->getObjects();
    }
}
