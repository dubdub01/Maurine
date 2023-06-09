<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegacyController extends AbstractController
{
    #[Route('/legacy', name: 'legacy')]
    public function index(): Response
    {
        return $this->render('legacy/legacy.html.twig', [
            'controller_name' => 'LegacyController',
        ]);
    }
}
