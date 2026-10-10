<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PerspectiveEditorBundle\Tests\Unit\ObjectCreationRules;

use OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules\RuleMatcher;
use PHPUnit\Framework\TestCase;

final class RuleMatcherTest extends TestCase
{
    public function testNoRuleAllowsEverything(): void
    {
        $matcher = new RuleMatcher([['path' => '/A', 'allowed_classes' => ['X']]]);

        self::assertNull($matcher->matchRule('/B'));
        self::assertTrue($matcher->isClassAllowed('/B', 'Anything'));
    }

    public function testNonRecursiveRuleMatchesOnlyExactPath(): void
    {
        $matcher = new RuleMatcher([['path' => '/A/', 'allowed_classes' => ['X']]]);

        self::assertNotNull($matcher->matchRule('/A'));
        self::assertNotNull($matcher->matchRule('/A/'));
        self::assertNull($matcher->matchRule('/A/B'));
        self::assertNull($matcher->matchRule('/AB'));
    }

    public function testRecursiveRuleMatchesDescendantsButNotSiblingPrefix(): void
    {
        $matcher = new RuleMatcher([['path' => '/A', 'allowed_classes' => ['X'], 'recursive' => true]]);

        self::assertNotNull($matcher->matchRule('/A'));
        self::assertNotNull($matcher->matchRule('/A/B/C'));
        self::assertNull($matcher->matchRule('/AB'));
    }

    public function testRecursiveRootRuleMatchesWholeTree(): void
    {
        $matcher = new RuleMatcher([['path' => '/', 'allowed_classes' => ['X'], 'recursive' => true]]);

        self::assertNotNull($matcher->matchRule('/'));
        self::assertNotNull($matcher->matchRule('/A'));
        self::assertNotNull($matcher->matchRule('/A/B'));
        self::assertFalse($matcher->isClassAllowed('/A/B', 'Y'));
    }

    public function testDepthIsCountedFromRulePath(): void
    {
        $matcher = new RuleMatcher([['path' => '/A', 'allowed_classes' => ['X'], 'recursive' => true, 'depth' => 1]]);

        self::assertNull($matcher->matchRule('/A'));
        self::assertNotNull($matcher->matchRule('/A/B'));
        self::assertNull($matcher->matchRule('/A/B/C'));
    }

    public function testDepthFromRoot(): void
    {
        $matcher = new RuleMatcher([['path' => '/', 'allowed_classes' => ['X'], 'recursive' => true, 'depth' => 2]]);

        self::assertNull($matcher->matchRule('/A'));
        self::assertNotNull($matcher->matchRule('/A/B'));
    }

    public function testDepthIsDroppedForNonRecursiveRule(): void
    {
        $matcher = new RuleMatcher([['path' => '/A', 'allowed_classes' => ['X'], 'depth' => 3]]);

        self::assertArrayNotHasKey('depth', $matcher->getRules()[0]);
        self::assertNotNull($matcher->matchRule('/A'));
    }

    public function testMoreSpecificPathWins(): void
    {
        $matcher = new RuleMatcher([
            ['path' => '/A', 'allowed_classes' => ['Outer'], 'recursive' => true],
            ['path' => '/A/B', 'allowed_classes' => ['Inner'], 'recursive' => true],
        ]);

        self::assertSame(['Inner'], $matcher->matchRule('/A/B/C')['allowed_classes']);
        self::assertSame(['Outer'], $matcher->matchRule('/A/C')['allowed_classes']);
    }

    public function testParentClassRuleHasPriorityAtEqualPathLength(): void
    {
        $matcher = new RuleMatcher([
            ['path' => '/A', 'allowed_classes' => ['Generic'], 'recursive' => true],
            ['path' => '/A', 'allowed_classes' => ['Variant'], 'recursive' => true, 'parent_class' => 'Product'],
        ]);

        self::assertSame(['Variant'], $matcher->matchRule('/A/p1', 'Product')['allowed_classes']);
        self::assertSame(['Generic'], $matcher->matchRule('/A/f1', 'Folderish')['allowed_classes']);
        self::assertSame(['Generic'], $matcher->matchRule('/A/f1')['allowed_classes']);
    }

    public function testForbiddenParentClassSkipsRule(): void
    {
        $matcher = new RuleMatcher([
            ['path' => '/A', 'allowed_classes' => ['X'], 'recursive' => true, 'forbidden_parent_classes' => ['Product']],
        ]);

        self::assertNull($matcher->matchRule('/A/p1', 'Product'));
        self::assertNotNull($matcher->matchRule('/A/p1', 'Other'));
        self::assertNotNull($matcher->matchRule('/A/f1'));
    }

    public function testEmptyAllowedClassesDeniesEverything(): void
    {
        $matcher = new RuleMatcher([['path' => '/System', 'allowed_classes' => []]]);

        self::assertFalse($matcher->isClassAllowed('/System', 'X'));
    }

    public function testAllowedClassesAreDeduplicated(): void
    {
        $matcher = new RuleMatcher([['path' => '/A', 'allowed_classes' => ['X', 'X', 'Y']]]);

        self::assertSame(['X', 'Y'], $matcher->getRules()[0]['allowed_classes']);
        self::assertTrue($matcher->isClassAllowed('/A', 'Y'));
        self::assertFalse($matcher->isClassAllowed('/A', 'Z'));
    }
}
