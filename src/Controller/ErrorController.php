<?php
// src/Controller/ErrorController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ErrorController extends AbstractController
{
    /**
     * @Route("/error", name="error")
     */
    public function show(): Response
    {
        return $this->render('error/error.html.twig');
    }
}
