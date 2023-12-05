<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\JsonConverter;
use App\Service\ApiLinker;

class AccueilController extends AbstractController {

    private $jsonConverter;
    private $apiLinker;

    public function __construct(ApiLinker $apiLinker, JsonConverter $jsonConverter) {
        $this->apiLinker = $apiLinker;
        $this->jsonConverter = $jsonConverter;
    }

    #[Route('/accueil', name: "accueil")]
    public function displayAccueil(Request $request) {

        $allPhotos = json_decode($this->apiLinker->readData("/photos"));
        
        return $this->render('accueil.html.twig', [
            'controller_name' => 'AccueilController',
            'allPhotos' => $allPhotos
        ]);
    }

}
