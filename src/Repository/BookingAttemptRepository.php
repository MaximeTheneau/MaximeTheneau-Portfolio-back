<?php

namespace App\Repository;

use App\Entity\BookingAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookingAttempt>
 *
 * @method BookingAttempt|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookingAttempt|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookingAttempt[]    findAll()
 * @method BookingAttempt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookingAttempt::class);
    }

    /**
     * Compte le nombre de réservations effectuées par une IP dans les X derniers jours
     */
    public function countByIpSince(string $ipAddress, int $days = 30): int
    {
        $since = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.ipAddress = :ip')
            ->andWhere('b.createdAt >= :since')
            ->setParameter('ip', $ipAddress)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Supprime les anciennes entrées (nettoyage)
     */
    public function deleteOlderThan(int $days = 60): int
    {
        $since = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('b')
            ->delete()
            ->where('b.createdAt < :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->execute();
    }
}
