<?php

namespace App\Repository;

use App\Entity\Log;
use App\Request\LogSearchRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function add(Log $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Log $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function searchLogs(LogSearchRequest $logSearchRequest): ?int
    {
        $queryBuilder = $this->createQueryBuilder('l');
        $queryBuilder->select('count(l.id)');
        $parameters = new ArrayCollection();
        if ($logSearchRequest->getServices()) {
            $queryBuilder
                ->Andwhere($queryBuilder->expr()->in("l.serviceName", ":services"));
            $parameters->add( new Parameter('services', $logSearchRequest->getServices()));
        }

        if ($logSearchRequest->getStatusCode()) {
            $queryBuilder = $queryBuilder
                ->Andwhere($queryBuilder->expr()->eq("l.responseCode", ":responseCode"));
            $parameters->add( new Parameter('responseCode', $logSearchRequest->getStatusCode()));
        }

        if ($logSearchRequest->getStartDate()) {
            $queryBuilder = $queryBuilder
                ->Andwhere($queryBuilder->expr()->gte("l.date", ":startDate"));
            $parameters->add( new Parameter('startDate', $logSearchRequest->getStartDate()));
        }

        if ($logSearchRequest->getEndDate()) {
            $queryBuilder
                ->Andwhere($queryBuilder->expr()->lte("l.date", ":endDate"));
            $parameters->add( new Parameter('endDate', $logSearchRequest->getEndDate()));
        }

        $query = $queryBuilder->setParameters($parameters)->getQuery();

        //dd($query->getSQL());
        $count = $query->getSingleScalarResult();

        return $count;
    }
}
