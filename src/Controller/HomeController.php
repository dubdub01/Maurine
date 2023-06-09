<?php

namespace App\Controller;

use App\Entity\Recipes;
use App\Service\StatsService;
use App\Repository\UserRepository;
use App\Repository\RecipesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * Permet d'afficher la page d'accueil
     *
     * @param RecipesRepository $recipeRepo
     * @param StatsService $stats
     * @return Response
     */
    #[Route('/', name: 'home')]
    public function index(RecipesRepository $recipeRepo,StatsService $stats, EntityManagerInterface $manager): Response
    {
        $users = $stats->getUsersCount();
        // getSingleScalarResult() Permet de récupérer une valeur et pas un array 
        $recipes=$stats->getRecipesCount();
       
        $comments=$stats->getCommentsCount();

        $user = $this->getUser();
        $recipes = $manager->getRepository(Recipes::class)->findAll();

        return $this->render('index.html.twig', [
            'recipes' => $recipeRepo->findByLast(3),
            'bestRecipes'=> $recipeRepo->findBestRecipes(3),
            'user'=> $user,
            'stats' => [
                'users'=>$users,
                'recipes'=>$recipes,
                'comments'=>$comments,
                
                
            ]
           
            
        ]);
    }
}
