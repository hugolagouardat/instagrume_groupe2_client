<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\ApiLinker;


class PageController extends AbstractController {

    private $apiLinker;
  
    public function __construct(ApiLinker $apiLinker) {
        $this->apiLinker = $apiLinker;
     }

    #[Route('/', methods: ['GET'], name: 'accueil')]
    public function displayConnexionPage() {
        return $this->render('accueil.html.twig', []);
    }

}