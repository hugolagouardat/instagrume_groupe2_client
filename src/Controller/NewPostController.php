<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Service\JsonConverter;
use App\Service\ToolFunctions;
use App\Service\ApiLinker;

class NewPostController extends AbstractController
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

    #[Route('/new_post', name: 'new_post')]
    public function displayNewPost(Request $request): Response
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

        return $this->render('new_post/new_post.html.twig', [
            'controller_name' => 'NewPostController',
            'actualUserName' =>  $userName,
            'actualUserRole' => $userRole,
            'actualUserId' => $userId
        ]);
    }

    #[Route('/add_post', name: 'add_post')]
    public function addPost(Request $request): Response
    {

        $description = $request->request->get('description');
        $session = $request->getSession();
        $token = $session->get('token-session');
        $payloadData = $this->toolFunctions->getPayload($token);
        if ($this->toolFunctions->isTokenExpirated($payloadData)) {
            return $this->redirect('/logout');
        }


        if ($payloadData != null) {

            $userName = $payloadData->username; // Nom de l'utilisateur
            $image = $request->files->get('photo');
            if (isset($image)) {                
                $imageData = file_get_contents($image->getPathname());
                $imageSend = base64_encode($imageData);

                $userId = $this->toolFunctions->getIdByUsername($request->request->get('comment_username'));

                if (isset($description)) {
                    $descript = $description;
                } else {
                    $descript = null;
                }

                $data = [
                    "image" => $imageSend,
                    "user_id" => $userId,
                    "description" => $descript
                ];

                // Formez le JSON à envoyer
                $jsonData = json_encode($data);
                $this->apiLinker->postData('/photos', $jsonData, $token);
                return $this->redirect('/');
            } else {
                return $this->render("new_post/new_post.html.twig", ['error' => 'Aucune image séléctionnée', 'actualUserName' =>  $userName]);
            }
        }

        // Gérez ici le cas où le formulaire n'est pas soumis ou s'il y a des erreurs
        return $this->redirect('/login');
    }
}
