<?php

namespace App\Repository;

use App\Entity\Recipes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipes>
 *
 * @method Recipes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipes[]    findAll()
 * @method Recipes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipes::class);
    }

    public function save(Recipes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Recipes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
    * @return Recipes[] Returns an array of Recipes objects
    */
    public function findByLast(int $limit): array
    {
       return $this->createQueryBuilder('r')
           ->select('r as recipe, r.slug, r.image, r.category, r.title, r.note, r.time, r.level, r.budget, AVG(c.note) as avgRatings')
           ->leftjoin('r.comments','c')
           ->groupBy('r')
           ->orderBy('r.id', 'DESC')
           ->setMaxResults($limit)
           ->getQuery()
           ->getResult()
       ;
    }

      /**
    * @return Recipes[] Returns an array of Recipes objects
    */
    public function findBestRecipes(int $limit): array
    {
       return $this->createQueryBuilder('r')
        ->select('r as recipe, r.slug, r.image, r.category, r.title, r.note, r.time, r.level, r.budget, AVG(c.note) as avgRatings')
        ->leftjoin('r.comments','c')
        ->groupBy('r')
        ->orderBy('avgRatings', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult()
       ;
    }

    /**
    * @return Recipes[] Returns an array of Recipes objects
    */
    public function findByCategory(string $category): array
    {
       return $this->createQueryBuilder('r')
           ->select('r as recipe, r.slug, r.image, r.category, r.title, r.note, r.time, r.level, r.budget, AVG(c.note) as avgRatings')
           ->leftjoin('r.comments','c')
           ->groupBy('r')
           ->orderBy('r.id', 'DESC')
           ->where('r.category= :category')
           ->setParameter('category', $category)
           ->getQuery()
           ->getResult()
       ;
    }

     /**
    * @return Recipes[] Returns an array of Recipes objects
    */
    public function findByUser(int $id): array
    {
       return $this->createQueryBuilder('r')
           ->select('r as recipe, r.slug, r.image, r.category, r.title, r.time, r.level, r.budget, AVG(c.note) as avgRatings')
           ->leftjoin('r.comments','c')
           ->orderBy('avgRatings', 'ASC')
           ->groupBy('r')
           ->where('r.idUser = :user')
           ->setParameter('user', $id)
           ->getQuery()
           ->getResult()
       ;
    }

    


    //Trouver une recette par la barre de recherche
    public function findRecipe(string $criteria)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->orX(
                        $qb->expr()->like('p.title', ':criteria'),
                        $qb->expr()->like('p.category', ':criteria'),
                        $qb->expr()->like('p.ingredient', ':criteria')
                    ),
                )
            )
            ->setParameter('criteria', '%' . $criteria . '%');
        return $qb
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Recipes[] Returns an array of Recipes objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Recipes
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
