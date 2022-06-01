<?php

namespace App\Service;

use App\Entity\Log;
use App\Repository\LogRepository;
use App\Request\LogSearchRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Contracts\Cache\CacheInterface;

class LogService
{

    const CACHE_KEY = "log.offset";

    private $entityManager;

    /**
     * @var LogRepository
     */
    private $logRepository;

    private $cacheEngine;


    public function __construct(EntityManagerInterface $entityManager, LogRepository $logRepository, CacheInterface $cacheEngine)
    {
        $this->entityManager = $entityManager;
        $this->logRepository = $logRepository;
        $this->cacheEngine = $cacheEngine;
    }

    public function process($fileLocation) {
        if (!file_exists($fileLocation)) {
            throw new FileNotFoundException("Invalid Location provided, file not found");
        }

        $fileResource = fopen($fileLocation, "r");

        $batchSize = 20;
        $counter = 0;

        $offset = $this->getLastPosition();

        fseek($fileResource, $offset + 1);
        while ($log = fgets($fileResource)) {
            $replace_pattern = "/[^\w\s\[\]\/:\-\+\.\"]/";
            $log = preg_replace($replace_pattern,"", $log);

            $logEntity = new Log();
            $this->parseAndSaveLog($logEntity, $log);

            $counter++;
            if ($counter == $batchSize) {
                $this->save();
                // cache the pointer
                $this->cacheProgress(ftell($fileResource));
                $counter = 0;
            }
        }

        if ($counter != 0) {
            $this->save();
            $this->cacheProgress(ftell($fileResource));
        }
    }

    private function save() {
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function parseAndSaveLog(Log $log, $data) {
        $pattern = "/^(\w+-?\w+)[\s-]+\[(.+)\]\s+\"(.+)\"\s+(\d+)$/";
        $status = preg_match($pattern, $data, $matches);

        if (!$status) {
            return false;
        }

        $log->setServiceName($matches[1]);

        $dateTime = new \DateTime($matches[2]);
        $log->setDate($dateTime);

        list($requestMethod, $endpoint, $httpVersion) = explode(" ", $matches[3]);
        $log->setRequestType($requestMethod);
        $log->setEndpoint($endpoint);
        $log->setHttpVersion($httpVersion);
        $log->setResponseCode($matches[4]);

        $this->entityManager->persist($log);
        return true;
    }

    private function cacheProgress($bytesRead) {
        $cache = $this->cacheEngine->getItem(self::CACHE_KEY);
        $cache->set($bytesRead);
        return $this->cacheEngine->save($cache);
    }

    private function getLastPosition() {
        $cache = $this->cacheEngine->getItem(self::CACHE_KEY);
        if (!$cache->isHit()) {
            return -1;
        }
        return $cache->get();
    }

    public function searchLogs(LogSearchRequest $logSearchRequest)
    {
        return $this->logRepository->searchLogs($logSearchRequest);
    }

}
