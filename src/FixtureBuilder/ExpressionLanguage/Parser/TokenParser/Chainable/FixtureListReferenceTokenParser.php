<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Definition\Value\ChoiceListValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ListNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\NotClonableTrait;

final class FixtureListReferenceTokenParser implements ChainableTokenParserInterface
{
    use NotClonableTrait;

    /** @internal */
    const REGEX = ListNameDenormalizer::REGEX;

    /**
     * @var string Unique token
     */
    private $token;

    public function __construct()
    {
        $this->token = uniqid(__CLASS__);
    }

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::LIST_REFERENCE_TYPE;
    }

    /**
     * Parses expressions such as '$username'.
     *
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function parse(Token $token)
    {
        $references = $this->buildReferences($token);

        return new ChoiceListValue($references);
    }

    /**
     * @param Token $token
     *
     * @throws ParseException
     *
     * @return array
     *
     * @example
     *  "@user_{alice, bob}" => ['user_alice', 'user_bob']
     */
    private function buildReferences(Token $token): array
    {
        $matches = [];
        $name = substr($token->getValue(), 1);
        if (false === $name) {
            throw ParseException::createForToken($token);
        }

        if (1 !== preg_match(self::REGEX, $name, $matches)) {
            throw ParseException::createForToken($token);
        }
        $listElements = preg_split('/\s*,\s*/', $matches['list']);

        $references = [];
        foreach ($listElements as $element) {
            $fixtureId = str_replace(
                sprintf('{%s}', $matches['list']),
                $element,
                $name
            );
            $references[] = new FixtureReferenceValue($fixtureId);
        }

        return $references;
    }
}
