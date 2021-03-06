<?php

namespace Comsa\BookingBundle\Repository;

use Comsa\BookingBundle\Entity\Reservable;
use Comsa\BookingBundle\Entity\ReservableInterval;
use Comsa\BookingBundle\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findAllForOverview()
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.reservableIntervals', 'ri')
            ->leftJoin('r.payment', 'p')
            ->orderBy('r.date', 'ASC')
            ->addOrderBy('ri.timeTo', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllForCriteria(?ReservableInterval $interval, $date, Reservable $reservable)
    {
        $params = [
            'reservable' => $reservable,
            'date' => $date->format('Y-m-d')
        ];

        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.reservableIntervals', 'i')
            ->innerJoin('r.reservables', 'rv')
            ->andWhere('rv = :reservable');

        if ($interval) {
            $qb
                ->andWhere('i = :interval');

            $params['interval'] = $interval;
        }

        $qb
            ->andWhere('r.date = :date')
            ->setParameters($params);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function findAllUpcomingReservationsByReservable(Reservable $reservable)
    {
        return $this->createQueryBuilder('i')
            ->where('i.date > :now')
            ->andWhere('i.reservable = :reservable')
            ->setParameter('reservable', $reservable)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Reservation[] Returns an array of Reservation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
