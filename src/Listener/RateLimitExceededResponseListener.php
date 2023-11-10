<?php

namespace App\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\Exception\RateLimitExceededException;

#[AsEventListener(event: KernelEvents::EXCEPTION, method: 'onException')]
final readonly class RateLimitExceededResponseListener
{
    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof RateLimitExceededException) {
            return;
        }

        throw new TooManyRequestsHttpException(
            retryAfter: $exception->getRetryAfter()->format('D, d M Y H:i:s T'),
            message: "You have exceeded your limit for calling this endpoint, try again after {$exception->getRetryAfter()->format('c')}",
        );
    }
}
