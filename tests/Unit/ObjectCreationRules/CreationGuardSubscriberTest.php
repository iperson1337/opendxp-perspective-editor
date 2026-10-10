<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PerspectiveEditorBundle\Tests\Unit\ObjectCreationRules;

use OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules\BypassCheckerInterface;
use OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules\CreationGuardSubscriber;
use OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules\RuleMatcher;
use OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules\RulesProvider;
use OpenDxp\Event\Model\DataObjectEvent;
use OpenDxp\Model\DataObject\Concrete;
use OpenDxp\Model\DataObject\Folder;
use OpenDxp\Model\Element\ValidationException;
use PHPUnit\Framework\TestCase;

final class CreationGuardSubscriberTest extends TestCase
{
    public function testAllowedClassPasses(): void
    {
        $subscriber = new CreationGuardSubscriber($this->provider([['path' => '/A', 'allowed_classes' => ['Product']]]));
        $event = $this->event('/A', 'Product');

        $subscriber->onPreAddOrUpdate($event);

        self::assertFalse($event->isPropagationStopped());
    }

    public function testForbiddenClassThrowsAndStopsPropagation(): void
    {
        $subscriber = new CreationGuardSubscriber($this->provider([['path' => '/A', 'allowed_classes' => []]]));
        $event = $this->event('/A', 'Product');

        try {
            $subscriber->onPreAddOrUpdate($event);
            self::fail('ValidationException expected');
        } catch (ValidationException) {
            self::assertTrue($event->isPropagationStopped());
        }
    }

    public function testBypassCheckerSkipsValidationWithoutLoadingRules(): void
    {
        $provider = $this->createMock(RulesProvider::class);
        $provider->expects(self::never())->method('createMatcher');

        $bypass = new class () implements BypassCheckerInterface {
            public function shouldBypass(): bool
            {
                return true;
            }
        };

        (new CreationGuardSubscriber($provider, [$bypass]))->onPreAddOrUpdate($this->event('/A', 'Product'));
    }

    public function testMatcherIsReusedUntilReset(): void
    {
        $provider = $this->createMock(RulesProvider::class);
        $provider->expects(self::exactly(2))->method('createMatcher')
            ->willReturnCallback(static fn (): RuleMatcher => new RuleMatcher([]));

        $subscriber = new CreationGuardSubscriber($provider);
        $subscriber->onPreAddOrUpdate($this->event('/A', 'Product'));
        $subscriber->onPreAddOrUpdate($this->event('/A', 'Product'));

        $subscriber->reset();
        $subscriber->onPreAddOrUpdate($this->event('/A', 'Product'));
    }

    /**
     * @param array<int,array<string,mixed>> $rules
     */
    private function provider(array $rules): RulesProvider
    {
        $provider = $this->createStub(RulesProvider::class);
        $provider->method('createMatcher')->willReturn(new RuleMatcher($rules));

        return $provider;
    }

    private function event(string $parentPath, string $className): DataObjectEvent
    {
        $parent = $this->createStub(Folder::class);
        $parent->method('getFullPath')->willReturn($parentPath);

        $object = $this->createStub(Concrete::class);
        $object->method('getParent')->willReturn($parent);
        $object->method('getClassName')->willReturn($className);

        return new DataObjectEvent($object);
    }
}
