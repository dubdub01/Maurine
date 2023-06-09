<?php

namespace App\Controller;

use App\Entity\Galery;
use App\Entity\Recipes;
use App\Entity\Comments;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    
    /**
     * Permet d'éditer un commentaire
     */
    #[Route('/comment/{id}', name:"edit_comment")]
    #[Security("(is_granted('ROLE_USER') and user === comment.getIdUser()) ", message:"Ce commentaire ne vous appartient pas, vous ne pouvez pas le modifier")]
    public function editComment(Comments $comment, Request $request, EntityManagerInterface $manager):Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le commentaire a bien été modifié !"
            );

            $recette = $comment->getIdRecipe()->getSlug();
    
            return $this->redirectToRoute('show_recipe',[
                'slug'=>$recette
            ]);
        }
        return $this->render("comment/edit.html.twig", [
            'myform' => $form->createView(),
            'recipe' => $comment->getIdRecipe()
        ]);
    }

    /**
     * Permet de supprimer un commentaire
     */
    #[Route('/comment/{id}/delete', name:"delete_comment")]
    #[Security("(is_granted('ROLE_USER') and user === comment.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Ce commentaire ne vous appartient pas, vous ne pouvez pas le supprimer")]
    public function deleteComment(Comments $comment, EntityManagerInterface $manager):Response
    {
        $this->addFlash(
            'success',
            "Le commentaire a été supprimé"
        );
        $recette = $comment->getIdRecipe()->getSlug();

        $manager->remove($comment);
        $manager->flush();

        return $this->redirectToRoute('show_recipe',[
            'slug'=>$recette
        ]);
    }

     /**
     * Permet de supprimer un commentaire à partir de l'admin
     */
    #[Route('admin/comment/{id}/delete', name:"admin_delete_comment")]
    #[Security("(is_granted('ROLE_USER') and user === comment.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Ce commentaire ne vous appartient pas, vous ne pouvez pas le supprimer")]
    public function deleteCommentAdmin(Comments $comment, EntityManagerInterface $manager):Response
    {
        $this->addFlash(
            'success',
            "Le commentaire a été supprimé"
        );

        $manager->remove($comment);
        $manager->flush();

        return $this->redirectToRoute('dashboard_comments');
    }

    /**
     * PErmet de supprimer une image de galerie 
     */
    #[Route ('admin/galery/{id}/delete', name:"delete_galery")]
    #[Security("(is_granted('ROLE_USER') and user === galery.getAuthor()) or is_granted('ROLE_ADMIN')", message:"Ce commentaire ne vous appartient pas, vous ne pouvez pas le supprimer")]
    public function deleteGalery(Galery $galery, EntityManagerInterface $manager):Response
    {
        $this->addFlash(
            'success',
            "Votre image a bien été supprimée"
        );

        unlink($this->getParameter('uploads_directory').'/'.$galery->getPicture());

        $manager->remove($galery);
        $manager->flush();

        $recette = $galery->getRecipe()->getSlug();

        return $this->redirectToRoute('show_recipe',[
            'slug'=>$recette
        ]);

    }


}
