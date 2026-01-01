<?php

namespace App\Twig\Components;

use App\Repository\RequestRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class RequestLatestPublished
{
    public function __construct(
        private readonly RequestRepository $requestRepository,
    ) {}

    /**
     * @return Request[]
     */
    public function getRequests(): array
    {
        return $this->requestRepository->findLatestByCreatedAt(4);
    }
}
