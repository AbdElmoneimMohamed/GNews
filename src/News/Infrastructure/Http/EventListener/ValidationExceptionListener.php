<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Http\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final readonly class ValidationExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (! $exception instanceof HttpException) {
            return;
        }

        $previous = $exception->getPrevious();
        if (! $previous instanceof ValidationFailedException) {
            return;
        }

        $violations = [];
        foreach ($previous->getViolations() as $violation) {
            $violations[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
                'invalidValue' => $violation->getInvalidValue(),
            ];
        }

        $response = new JsonResponse(
            [
                'error' => 'Validation failed',
                'violations' => $violations,
            ],
            Response::HTTP_BAD_REQUEST
        );

        $event->setResponse($response);
    }
}
