<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\LogSearchRequest;
use App\Service\LogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LogController extends AbstractController
{
    private $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    #[Route('/count', methods: ['GET'])]
    public function index(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $logSearchRequest = new LogSearchRequest($validator);

        $logSearchRequest->setServices($request->query->get("serviceNames"));
        $statusCode = $request->query->get("statusCode");
        $logSearchRequest->setStatusCode(!empty($statusCode) ? intval($statusCode) : null);
        $logSearchRequest->setStartDate($request->query->get("startDate"));
        $logSearchRequest->setEndDate($request->query->get("endDate"));

        $violations = $logSearchRequest->validate();
        if (count($violations) > 0) {
            $violation = $violations->get(0);
            return $this->json([$violation->getPropertyPath() => $violation->getMessage()])->setStatusCode(400);
        }

        $count = $this->logService->searchLogs($logSearchRequest);
        return $this->json(["counter" => $count]);
    }
}
