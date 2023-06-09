<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Recipes;
use App\Entity\Comments;
use App\Repository\UserRepository;
use App\Repository\RecipesRepository;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Egulias\EmailValidator\Result\Reason\CommentsInIDRight;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashbController extends AbstractController
{
    /**
     * Afficchage de la page d'accueil du dashboard 
     *
     * @return Response
     */
    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted("ROLE_ADMIN")]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
           
        ]);
    }

    /**
     * Affichage de la liste des recettes (administration)
     *
     * @param RecipesRepository $repo
     * @return Response
     */
    #[Route('/dashboard/recipes', name:'dashboard_recipes')]
    #[IsGranted("ROLE_ADMIN")]
    public function dash_recipes(RecipesRepository $repo ):Response
    {
        $recettes = $repo->findAll();
        return $this->render('dashboard/recipes.html.twig',[
            'recettes'=>$recettes
        ]);
    }

    /**
     * Affichage de la liste des utilisateurs (administrateur)
     *
     * @param UserRepository $repo
     * @return Response
     */
    #[Route('/dashboard/users', name:'dashboard_users')]
    #[IsGranted("ROLE_ADMIN")]
    public function dash_users(UserRepository $repo ):Response
    {
        $users = $repo->findAll();
        return $this->render('dashboard/users.html.twig',[
            'users'=>$users
        ]);
    }

    /**
     * Supprimer un user à partir de l'admin
     */
    #[Route("/dashboard/users/{id}/delete", name:"profile_delete")]
    #[IsGranted("ROLE_ADMIN")]
    public function deleteAccount(User $user,  EntityManagerInterface $manager, ):Response
    {
        $this->addFlash(
            'success',
            "L'utilisateur <strong>{$user->getId()}</strong> a bien été supprimé"
        );

        if($user->getAvatar())
        {
            unlink($this->getParameter('uploads_directory').'/'.$user->getAvatar());
        }
        
        $recipes = $user->getRecettes();
        foreach($recipes as $recettes)
        {
            $manager->remove($recettes);
            $manager->flush();
        }
        // $comments = $user->getComments();
        // foreach($comments as $comment)
        // {
        //     $comment->setIdUser(null);

        //     $manager->persist($comment);
        //     $manager->flush();
        // }
        
        $manager->remove($user);
        $manager->flush();

        return $this->redirectToRoute('recettes_index');


    }

    /**
     * Liste des commentaires dans l'admin
     *
     * @param CommentsRepository $repo
     * @return Response
     */
    #[Route('/dashboard/comments', name:'dashboard_comments')]
    #[IsGranted("ROLE_ADMIN")]
    public function dash_comments(CommentsRepository $repo ):Response
    {
        $comments = $repo->findAll();
        return $this->render('dashboard/comments.html.twig',[
            'comments'=>$comments
        ]);
    }
}
