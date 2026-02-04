<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Twig\Environment;

class ExceptionListener
{
    public function __construct(private readonly Environment $twig) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $exception = $event->getThrowable();

        if (str_starts_with($route, 'api_')) {

            $responseStatus = Response::HTTP_BAD_REQUEST;
            $exceptionCode = intval($exception->getCode());
            if ($exceptionCode >= 400 && $exceptionCode <= 599) {
                $responseStatus = $exceptionCode;
            }

            $responseData = [
                'error' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ];
            if ($exception instanceof ApiException) {
                $responseData['code'] = $exception->getErrorCode();

                if ($exception->getPrevious() != null) {
                    $responseData['cause'] = $exception->getPrevious()->getMessage();
                }
            }

            $event->setResponse(new JsonResponse($responseData, $responseStatus));
        }
    }
}
