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

            // Obtenir les photos de l'utilisateur
            $photosResponse = $this->apiLinker->readData("/photos/user/" . $userData['id']);
            $allPhotos = json_decode($photosResponse, true);
            $allComments = json_decode($this->apiLinker->readData("/commentaires"));}

        return $this->render('search_results.html.twig', [
            'controller_name' => 'RechercheController',
            'actualUserName' =>  $userName,
            'actualUserRole' => $userRole,
            'actualUserId' => $userId,
            'user' => $userData,
            'allPhotos' => $allPhotos,
            'allComments' => $allComments

        ]);
    }
    #[Route('/add_comment', name: 'add_comment')]
    public function addCommentaire(Request $request): Response
    {

        $textToAdd = $request->request->get('commentaire');
        $session = $request->getSession();
        $token = $session->get('token-session');
        $payloadData = $this->toolFunctions->getPayload($token);
        if ($this->toolFunctions->isTokenExpirated($payloadData)) {
            return $this->redirect('/logout');
        }


        if ($payloadData != null) {

            $userId = $this->toolFunctions->getIdByUsername($request->request->get('comment_username'));
            $senderPostId = intval($request->request->get('comment_photo_id'));
            $data = [
                "user_id" => $userId,
                "description" => $textToAdd
            ];

            // Formez le JSON à envoyer
            $jsonData = json_encode($data);
            $this->apiLinker->postData('/photos/' . $senderPostId . '/commentaires', $jsonData, $token);
            return $this->redirect('/');
        }

        // Gérez ici le cas où le formulaire n'est pas soumis ou s'il y a des erreurs

        return $this->redirect('/login');
    }

    #[Route('/rep_comment/{idParentComment}/{userToAnnote}', name: 'rep_comment')]
    public function addReponse(Request $request, $idParentComment, $userToAnnote): Response
    {

        $textToAdd = $request->request->get('commentaire');
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

            $userId = $this->toolFunctions->getIdByUsername($request->request->get('comment_username'));
            $senderPostId = intval($request->request->get('comment_photo_id'));
            if ($userToAnnote != 'noUserToAnnote') {
                $descript = ('@'.$userToAnnote.' '.$textToAdd);
            }
            else {
                $descript = $textToAdd;
            }
            $data = [
                "user_id" => $userId,
                "description" => $descript,
                "parent_comment_id" => $idParentComment
            ];

            // Formez le JSON à envoyer
            $jsonData = json_encode($data);
            $this->apiLinker->postData('/photos/' . $senderPostId . '/commentaires', $jsonData, $token);

            return $this->redirect('/');
        }

        return $this->redirect('/login');
    }

    #[Route('/update_comment/{commentId}', name: 'update_comment')]
    public function updateComment(Request $request, $commentId): Response
    {
        $newComment = $request->request->get('commentaire');
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
            
            if (isset($newComment)) {                
                $data = [
                    "description" => $newComment
                ];
            } else {
                $data = [];
            }

            // Formatez les données en JSON
            $jsonData = json_encode($data);
            $this->apiLinker->putData('/commentaires/' . $commentId, $jsonData, $token);

            return $this->redirect('/');
        }

        return $this->redirect('/login');
    }
}
