<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class StatsService {

    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function getUsersCount(): int
    {
        return $this->manager->createQuery("SELECT COUNT(u) FROM App\Entity\User u")->getSingleScalarResult();
    }

    public function getRecipesCount():int
    {
        return $this->manager->createQuery("SELECT COUNT(r) FROM App\Entity\Recipes r")->getSingleScalarResult();

    }

    // public function getBookingsCount():int
    // {
    //     return $this->manager->createQuery("SELECT COUNT(b) FROM App\Entity\Booking b")->getSingleScalarResult();

    // }

    public function getCommentsCount():int
    {
        return $this->manager->createQuery("SELECT COUNT(c) FROM App\Entity\Comments c")->getSingleScalarResult();

    }

    public function getRecipesStats(string $direction):array
    {
        return $this->manager->createQuery(
            "SELECT AVG(c.note) as note, r.title, r.id, u.pseudo, u.avatar, r.image, r.category, r.time, r.level, r.budget, r.slug
            FROM App\Entity\Comments c
            JOIN c.idRecipe r
            JOIN r.idUser u
            GROUP BY r
            ORDER BY note ".$direction
        )->setMaxResults(3)->getResult();
    }
}