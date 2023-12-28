<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ApiLinker;
use App\Service\ToolFunctions;
use App\Service\JsonConverter;


class RechercheController extends AbstractController
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


    #[Route('/search', methods: ['GET'])]
    public function searchUser(Request $request): Response
    {
        $username = $request->query->get('username');

        // Utilisation de ApiLinker pour obtenir les données de l'utilisateur
        $apiResponse = $this->apiLinker->readData("/users/" . $username);


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
        } else {
            $userRole = null;
            $userName = null;
            $userId = null;
            
        }

        if ($apiResponse) {
            $userData = json_decode($apiResponse, true);

        }

        return $this->render('search_results.html.twig', [
            'controller_name' => 'RechercheController',
            'actualUserName' =>  $userName,
            'actualUserRole' => $userRole,
            'actualUserId' => $userId,
            'user' => $userData,
            'userSearch' => $username
        ]);
    }

    #[Route('/search_profil/{userName}', name: 'search_profil')]
    public function searchProfil(Request $request, $userName): Response
    {
        $session = $request->getSession();
        $token = $session->get('token-session');
        $payloadData = $this->toolFunctions->getPayload($token);
        if ($this->toolFunctions->isTokenExpirated($payloadData)) {
            return $this->redirect('/logout');
        }

        if ($payloadData != null) {
            $actualUserName = $payloadData->username; // Nom de l'utilisateur
            $actualUserRole = $payloadData->roles[0]; // Rang de l'utilisateur
            $actualUserId = $this->toolFunctions->getIdByUsername($actualUserName);

            
        } else {
            $actualUserRole = null;
            $actualUserName = null;
        }

        $userId = $this->toolFunctions->getIdByUsername($userName);
        $user = $this->toolFunctions->getUserByUsername($userName);

        $allPhotos = json_decode($this->apiLinker->readData("/photos"));
        $allPhotosUser = $this->toolFunctions->getPhotoByUserId($userId);

        return $this->render('profil/profil.html.twig', [
            'controller_name' => 'ProfilController',
            'allPhotos' => $allPhotos,
            'actualUserName' =>  $actualUserName,
            'actualUserRole' => $actualUserRole,
            'actualUserId' => $actualUserId,
            'user' => $user,
            'userId' => $userId,
            'allPhotosUser' => $allPhotosUser
        ]);
    }
}
