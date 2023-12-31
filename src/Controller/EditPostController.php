<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Service\JsonConverter;
use App\Service\ToolFunctions;
use App\Service\ApiLinker;

class EditPostController extends AbstractController
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

    #[Route('/edit_post/{photoId}', name: 'edit_post')]
    public function displayEditPost(Request $request, $photoId): Response
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
        } else {
            $userRole = null;
            $userName = null;
            $userId = null;
        }

        $photo = $this->toolFunctions->getPhotoById($photoId);

        return $this->render('edit_post/edit_post.html.twig', [
            'controller_name' => 'EditPostController',
            'actualUserName' =>  $userName,
            'actualUserRole' => $userRole,
            'actualUserId' => $userId,
            'idPhoto' => $photoId,
            'photo' => $photo
        ]);
    }

    #[Route('/update_post/{photoId}', name: 'update_post')]
    public function updatePost(Request $request, $photoId): Response
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
        } else {
            $userRole = null;
            $userName = null;
            $userId = null;
            return $this->redirect('/login');
        }

        if ($payloadData != null) {
            // Gestion de l'image
            $image = $request->files->get('photo');
            $newDescription = $request->request->get('description');

            $imageData = isset($image) ? file_get_contents($image->getPathname()) : null;
            $imageSend = isset($image) ? base64_encode($imageData) : null;

            $data = [
                "image" => isset($image) ? $imageSend : null,
                "description" => isset($newDescription) ? $newDescription : null
            ];

            // Formatez les données en JSON
            $jsonData = json_encode($data);
            $this->apiLinker->putData('/photos/' . $photoId, $jsonData, $token);

            return $this->redirect('/');
        }

        return $this->redirect('/login');
    }
}
