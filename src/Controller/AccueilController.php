<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\JsonConverter;
use App\Service\ApiLinker;

class AccueilController extends AbstractController
{
    private $jsonConverter;
    private $apiLinker;

    public function __construct(ApiLinker $apiLinker, JsonConverter $jsonConverter) {
        $this->apiLinker = $apiLinker;
        $this->jsonConverter = $jsonConverter;
    }

    #[Route('/', name: 'app_accueil')]
    public function displayAccueil(): Response
    {

        $allPhotos = json_decode($this->apiLinker->readData("/photos"));
        $allComments = json_decode($this->apiLinker->readData("/commentaires"));
        
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
            'allPhotos' => $allPhotos,
            'allComments' => $allComments
        ]);
    }
}
