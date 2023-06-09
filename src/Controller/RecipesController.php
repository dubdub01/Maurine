<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Galery;
use App\Entity\Recipes;
use App\Entity\Comments;
use App\Form\GaleryType;
use App\Form\RecipeType;
use App\Form\SearchType;
use Symfony\Flex\Recipe;
use App\Form\CommentType;
use App\Form\ModifyRecipeType;
use App\Service\PaginationService;
use App\Repository\RecipesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RecipesController extends AbstractController
{
    // /**
    //  * Affiche les recettes du site sans formulaire de recherche
    //  *
    //  * @param RecipesRepository $repo
    //  * @return Response
    //  */
    // #[Route('/recettes/{page<\d+>?1}', name: 'recettes_index')]
    // public function index(PaginationService $pagination, $page): Response
    // {
    //     $pagination -> setEntityClass(Recipes::class)
    //     ->setPage($page)
    //     ->setLimit(6);

    //     return $this->render('recipes/index.html.twig', [
    //         'pagination' => $pagination,
    //     ]);
    // }

    /**
     * Affiche les recettes du site
     *
     * @param RecipesRepository $repo
     * @return Response
     */
    #[Route('/recettes/{page<\d+>?1}', name: 'recettes_index')]
    public function index(Request $request, PaginationService $pagination, $page, RecipesRepository $repo, EntityManagerInterface $manager): Response
    {
        $searchForm = $this->createForm(SearchType::class);
        
        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()){
            
            $criteria = $searchForm['search']->getData();
            $recettes = $manager->getRepository(Recipes::class)->findRecipe($criteria);
            $user = $this->getUser();
            $likes = $manager->getRepository(Like::class)->findAll();

            return $this->render('recipes/search.html.twig', [
                'search'=>$searchForm->createView(),
                'recipes'=>$recettes,
                'criteria'=>$criteria,
                'user'=> $user,
                'likes'=>$likes
            ]);
        }else{
            $pagination -> setEntityClass(Recipes::class)
                    ->setPage($page)
                    ->setLimit(9);
            
            $user = $this->getUser();
            $likes = $manager->getRepository(Like::class)->findAll();

            return $this->render('recipes/index.html.twig', [
                'search'=>$searchForm->createView(),
                'pagination'=> $pagination,
                'user'=> $user,
                'likes'=>$likes
            ]);
        }
        

        
    }

    /**
     * Afficher les recettes d'une catégorie 
     */
    #[Route('recettes/category={category}', name:'recettes_category')]
    public function showCategory(RecipesRepository $repo, Request $request, EntityManagerInterface $manager):Response
    {
        $category=$request->get('category');
        $recettes = $repo->findByCategory($category);
        $user = $this->getUser();
        $likes = $manager->getRepository(Like::class)->findAll();
        
        return $this->render('recipes/category.html.twig', [
            'recettes' => $recettes,
            'user'=> $user,
            'likes'=>$likes
        ]);
    }
    
    /**
     * Trouver les recettes d'un utilisateur
     */
    #[Route('recettes/user/{id}', name:'recipes_of')]
    public function recetteOfUser(RecipesRepository $repo, Request $request):Response
    {
        $user=$request->get('id');
        $recettes = $repo->findByUser($user);
        return $this->render('recipes/userRecipes.html.twig',[
            'recettes'=>$recettes
        ]);
    }

    /**
     * Permet d'afficher le formulaire de création de l'ajout d'une recette
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recettes/new', name: "new_recipe")]
    #[IsGranted("ROLE_USER")]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipes();

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**Gestion de l'image de couverture */
            $file = $form['image']->getData();
            if (!empty($file)) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin;Latin-ASCII;[^A-Za-z0-9_]remove;Lower()', $originalFilename);
                $newFilename = $safeFilename . "-" . uniqid() . "." . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $e->getMessage();
                }
                $recipe->setImage($newFilename);
            }

            $recipe->setIdUser($this->getUser());

            $manager->persist($recipe);
            $manager->flush();


            /**
             * Message flash pour alerter l'utilisateur de l'état de la tâche
             */
            $this->addFlash(
                'success',
                "L'annonce <strong>{$recipe->getTitle()} - {$recipe->getCategory()}</strong> a bien été enregistrée!"
            );

            return $this->redirectToRoute('show_recipe', [
                'slug' => $recipe->getSlug()
            ]);
        }

        return $this->render("recipes/new.html.twig", [
            'myform' => $form->createView()
        ]);
    }

    /**
     * Permet d'éditer une recette
     */
    #[Route('/recettes/{slug}/edit', name:'edit_recipe')]
    #[Security("(is_granted('ROLE_USER') and user === recipe.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Cette recette ne vous appartient pas, vous ne pouvez pas la modifier")]
    public function edit(Request $request, EntityManagerInterface $manager, Recipes $recipe):Response
    {
        $form = $this->createForm(ModifyRecipeType::class, $recipe);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            
             /**Gestion de l'image de couverture */
             $file = $form['image']->getData();
             if (!empty($file)) {


                 $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                 $safeFilename = transliterator_transliterate('Any-Latin;Latin-ASCII;[^A-Za-z0-9_]remove;Lower()', $originalFilename);
                 $newFilename = $safeFilename . "-" . uniqid() . "." . $file->guessExtension();
                 try {
                     $file->move(
                         $this->getParameter('uploads_directory'),
                         $newFilename
                     );
                 } catch (FileException $e) {
                     return $e->getMessage();
                 }
                 $recipe->setImage($newFilename);
             }else{
                $recipe->setImage($recipe->getImage());
             }
 
             $recipe->setIdUser($this->getUser());
      
 
             $manager->persist($recipe);
             $manager->flush();
 
             /**
              * Message flash pour alerter l'utilisateur de l'état de la tâche
              */
             $this->addFlash(
                 'success',
                 "L'annonce <strong>{$recipe->getTitle()} - {$recipe->getCategory()}</strong> a bien été enregistrée!"
             );
 
             return $this->redirectToRoute('show_recipe', [
                 'slug' => $recipe->getSlug()
             ]);
        }

        return $this->render("recipes/edit.html.twig", [
            'myform' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher une recette en particulier
     */
    #[Route('/recettes/{slug}', name:'show_recipe')]
    public function showRecipe(string $slug, Recipes $recipe, Request $request,EntityManagerInterface $manager):Response
    {

        
        $comment = new Comments();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setIdUser($this->getUser())
                    ->setDate(new \DateTime())
                    ->setIdRecipe($recipe);

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le commentaire a bien été enregistré!"
            );

            return $this->redirectToRoute('show_recipe', [
                'slug' => $recipe->getSlug(),
                
            ]);

        }

        $galery = new Galery();
        $formGalery = $this->createForm(GaleryType::class, $galery);
        $formGalery->handleRequest($request);
        if($formGalery->isSubmitted() && $formGalery->isValid()){
            $galery ->setRecipe($recipe)
                    ->setAuthor($this->getUser());
        
            $manager->persist($galery);
            $manager->flush();

            $this->addFlash(
                'success',
                "La photo a bien été enregistrée!"
            );

            return $this->redirectToRoute('show_recipe', [
                'slug' => $recipe->getSlug(),
            ]);
        }

        return $this->render('recipes/showRecipe.html.twig',[
            'recipe'=> $recipe,
            'myform' => $form->createView(),
            'formgalery'=> $form->createView()
        ]);
    
        
    }

    /**
     * Permet dd'ajouter une photo à la galerie de résultat
     */
    #[Route('/recettes/{slug}/galery', name:'galery_recipe')]
    #[IsGranted("ROLE_USER")]
    public function addGalery(string $slug, Recipes $recipe, Request $request,EntityManagerInterface $manager):Response
    {

        $galery = new Galery();
        $form = $this->createForm(GaleryType::class, $galery);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $file = $form['picture']->getData();
            if (!empty($file)) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin;Latin-ASCII;[^A-Za-z0-9_]remove;Lower()', $originalFilename);
                $newFilename = $safeFilename . "-" . uniqid() . "." . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $e->getMessage();
                }
                $galery->setPicture($newFilename);
            }

            $galery ->setRecipe($recipe)
                ->setAuthor($this->getUser());
        
            $manager->persist($galery);
            $manager->flush();

            $this->addFlash(
                'success',
                "La photo a bien été enregistrée!"
            );

            return $this->redirectToRoute('show_recipe', [
                'slug' => $recipe->getSlug(),
            ]);
        }

        return $this->render('recipes/addGalery.html.twig',[
            'recipe'=> $recipe,
            'myform' => $form->createView(),
            
        ]);
    
        
    }

    /**
     * Permet de supprimer une recette
     */
    #[Route('/recettes/{slug}/delete', name:"delete_recipe")]
    #[Security("(is_granted('ROLE_USER') and user === recipe.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Cette recette ne vous appartient pas, vous ne pouvez pas la supprimer")]
    public function deleteRecipe(Recipes $recipe, EntityManagerInterface $manager):Response
    {
        $this->addFlash(
            'success',
            "L'annonce <strong>{$recipe->getTitle()}</strong> a été supprimée"
        );
        
            
        
        unlink($this->getParameter('uploads_directory').'/'.$recipe->getImage());
     
        
        $manager->remove($recipe);
        $manager->flush();

        return $this->redirectToRoute('recettes_index');
    }
    

}
