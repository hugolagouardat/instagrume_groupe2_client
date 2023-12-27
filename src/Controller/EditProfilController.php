<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Service\JsonConverter;
use App\Service\ToolFunctions;
use App\Service\ApiLinker;

class EditProfilController extends AbstractController
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

    #[Route('/edit_profil', name: 'edit_profil')]
    public function displayEditProfil(Request $request): Response
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

        return $this->render('edit_profil/edit_profil.html.twig', [
            'controller_name' => 'EditProfilController',
            'actualUserName' =>  $userName,
            'actualUserRole' => $userRole,
            'actualUserId' => $userId,
            'user' => $user
        ]);
    }

    #[Route('/update_profil/{userId}', name: 'update_profil')]
    public function updatePost(Request $request, $userId): Response
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
            $user = $this->toolFunctions->getUserByUsername($userName);

        } else {
            $userRole = null;
            $userName = null;
            $userId = null;
            return $this->redirect('/login');
        }

        if ($payloadData != null) {
            // Gestion de l'image
            $image = $request->files->get('avatar');
            $newDescription = $request->request->get('description');
            $newPassword = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            $imageData = isset($image) ? file_get_contents($image->getPathname()) : null;
            $imageSend = isset($image) ? base64_encode($imageData) : null;

            if ($newPassword != $confirmPassword) {
                return $this->render("edit_profil/edit_profil.html.twig", ['error' => 'Les mots de passes ne correspondent pas.', 'actualUserName' =>  $userName, 'user' =>  $user]);
            }            

            $data = [
                "avatar" => isset($image) ? $imageSend : null,
                "description" => isset($newDescription) ? $newDescription : null,
                "password" => isset($newPassword) ? $newPassword : null
            ];


            // Formatez les données en JSON
            $jsonData = json_encode($data); 
            $this->apiLinker->putData('/users/' . $userId, $jsonData, $token);

            return $this->redirect('/profil');
        }

        return $this->redirect('/login');
    }
}
