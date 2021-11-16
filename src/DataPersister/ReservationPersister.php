<?php

namespace App\DataPersister;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;

class ReservationPersister implements DataPersisterInterface
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function supports($data): bool
    {
        return $data instanceof Reservation;
    }

    public function persist($data)
    {
        // 1. mettre une date de création de l'utilisateur
        $data->setCreatedAt(new \DateTimeImmutable());
        // 2. demander a doctrine de persister
        $this->em->persist($data);
        $this->em->flush();
    }

    public function remove($data)
    {
        // 1. demande à doctrine de supprimer l'article
        $this->em->remove($data);
        $this->em->flush();
    }
}
