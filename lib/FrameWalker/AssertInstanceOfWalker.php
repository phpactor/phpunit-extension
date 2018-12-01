<?php

namespace Phpactor\Extension\PHPUnit\FrameWalker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable as ParserVariable;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Type;

class AssertInstanceOfWalker implements FrameWalker
{
    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    public function __construct()
    {
        $this->symbolFactory = new SymbolFactory();
    }

    public function canWalk(Node $node): bool
    {
        if ($node instanceof ScopedPropertyAccessExpression) {
            $scopeResolutionQualifier = $node->scopeResolutionQualifier;

            if (!$scopeResolutionQualifier instanceof QualifiedName) {
                return false;
            }

            $resolvedName = $scopeResolutionQualifier->getResolvedName();
            if ((string) $resolvedName !== 'PHPUnit\Framework\Assert') {
                return false;
            }

            return true;
        }

        if ($node instanceof MemberAccessExpression) {
            $memberName = $node->memberName;

            if (!$memberName instanceof Token) {
                return false;
            }

            // we havn't got the facility to check if we are extending the TestCase
            // here, so just assume that any method named this way is belonging to
            // PHPUnit
            if ('assertInstanceOf' === $memberName->getText($node->getFileContents())) {
                return true;
            }
        }

        return false;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        $callExpression = $node->parent;
        assert($callExpression instanceof CallExpression);
        $expresionList = $callExpression->argumentExpressionList;

        if (!$expresionList instanceof ArgumentExpressionList) {
            return $frame;
        }

        $elements = iterator_to_array($expresionList->getElements());

        if (!isset($elements[0])) {
            return $frame;
        }

        if (!isset($elements[1])) {
            return $frame;
        }

        $type = $this->resolveType($elements[0]);

        if (null === $type) {
            return $frame;
        }

        $name = $this->resolveVariableName($elements[1]);

        if (null === $name) {
            return $frame;
        }

        $frame->locals()->add(Variable::fromSymbolContext(
            $this->symbolFactory->context(
                $name,
                $node->getStart(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE,
                    'type' => $type,
                ]
            )
        ));

        return $frame;
    }

    private function resolveType(ArgumentExpression $element): ?Type
    {
        $expression = $element->expression;
        if ($expression instanceof ScopedPropertyAccessExpression) {
            $memberName = $expression->memberName;

            if (!$memberName instanceof Token) {
                return null;
            }

            if ('class' === $memberName->getText($element->getFileContents())) {
                $scopeResolutionQualifier = $expression->scopeResolutionQualifier;

                if (!$scopeResolutionQualifier instanceof QualifiedName) {
                    return null;
                }

                return Type::fromString((string) $scopeResolutionQualifier->getResolvedName());
            }
        }

        if ($expression instanceof StringLiteral) {
            return Type::fromString($expression->getStringContentsText());
        }

        return null;
    }

    private function resolveVariableName(ArgumentExpression $element): ?string
    {
        $expression = $element->expression;
        if (!$expression instanceof ParserVariable) {
            return null;
        }

        $name = $expression->name;

        if (!$name instanceof Token) {
            return null;
        }

        return substr((string) $name->getText($element->getFileContents()), 1);
    }
}
