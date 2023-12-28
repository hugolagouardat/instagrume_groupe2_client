<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Service\JsonConverter;
use App\Service\ToolFunctions;
use App\Service\ApiLinker;

class ProfilController extends AbstractController
{
    private $jsonConverter;
    private $apiLinker;
    private $toolFunctions;

    public function __construct(ApiLinker $apiLinker, JsonConverter $jsonConverter, ToolFunctions $toolFunctions)
    {
        $this->apiLinker = $apiLinker;
        $this->jsonConverter = $jsonConverter;
        $this->toolFunctions = $toolFunctions;
    }

    #[Route('/profil', name: 'profil')]
    public function displayProfil(Request $request): Response
    {
        $session = $request->getSession();
        $token = $session->get('token-session');
        $payloadData = $this->toolFunctions->getPayload($token);
        if ($this->toolFunctions->isTokenExpirated($payloadData)) {
            return $this->redirect('/logout');
        }
        if ($payloadData != null) {
            $userName = $payloadData->username; // Nom de l'utilisateur
            $userRole = $payloadData->roles[0]; // Rang de l'utilisateur
            $userId = $this->toolFunctions->getIdByUsername($userName);
            $user = $this->toolFunctions->getUserByUsername($userName);

        } else {
            $userRole = null;
            $userName = null;
            $userId = null;
        }

        $allPhotos = json_decode($this->apiLinker->readData("/photos"));
        $allPhotosUser = $this->toolFunctions->getPhotoByUserId($userId);

        return $this->render('profil/profil.html.twig', [
            'controller_name' => 'ProfilController',
            'allPhotos' => $allPhotos,
            'actualUserName' =>  $userName,
            'actualUserRole' => $userRole,
            'actualUserId' => $userId,
            'user' => $user,
            'allPhotosUser' => $allPhotosUser
        ]);
    }
}
