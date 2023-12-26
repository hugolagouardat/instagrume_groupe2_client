<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\ToolFunctions;
use App\Service\JsonConverter;
use App\Service\ApiLinker;

class ConnexionController extends AbstractController {

    private $jsonConverter;
    private $apiLinker;
    private $toolFunctions;

    public function __construct(ApiLinker $apiLinker, JsonConverter $jsonConverter, ToolFunctions $toolFunctions) {
        $this->apiLinker = $apiLinker;
        $this->jsonConverter = $jsonConverter;
        $this->toolFunctions = $toolFunctions;
    }

    #[Route('/login', methods: ['GET'])]
    public function displayConnexion(Request $request){

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

        return $this->render('login.html.twig', [
            'controller_name' => 'ConnexionController',
            'actualUserName' =>  $userName,
            'actualUserRole' => $userRole,
            'actualUserId' => $userId
        ]);
    }

    #[Route('/login', methods: ['POST'])]
    public function connexion(Request $request) {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
    
        if (!empty($username) && !empty($password)) {
            $data = $this->jsonConverter->encodeToJson(['username' => $username, 'password' => $password]);
            $response = $this->apiLinker->postData('/login', $data, null);
            $responseObject = json_decode($response);
    
            // Vérifiez si le token est récupéré avec succès
            if (isset($responseObject->token)) {
                // Stockez le token dans la session
                $session = $request->getSession();
                $session->set('token-session', $responseObject->token);
    
                // Redirigez vers la page d'accueil ou une autre page après la connexion réussie
                return $this->redirect('/');
                
            } else if ($response == 'Les champs ne doivent pas être vide') {
                return $this->render("login.html.twig", ['error' => 'Les champs ne doivent pas être vide.', 'actualUserName' => null]);
            } else if ($response == 'Username invalide') {
                return $this->render("login.html.twig", ['error' => 'Identifiant invalide.', 'actualUserName' => null]);
            } else if ($response == 'Password invalide') {
                return $this->render("login.html.twig", ['error' => 'Mot de passe invalide.', 'actualUserName' => null]);
            }
        }
        
        return $this->redirect('/login');
    }
    

    #[Route('/logout', methods: ['GET'])]
    public function deconnexion(Request $request) {
        $session = $request->getSession();
        $session->remove('token-session');
        $session->clear();

        return $this->redirect('/');
    }


    #[Route('/register', methods: ['GET'])]
    public function displayRegister(Request $request){

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

        return $this->render('register.html.twig', [
            'controller_name' => 'ConnexionController',
            'actualUserName' =>  $userName,
            'actualUserRole' => $userRole,
            'actualUserId' => $userId
        ]);

        
    }
    #[Route('/register', methods: ['POST'])]
public function register(Request $request) {
    $username = $request->request->get('username');
    $description = $request->request->get('description');
    $password = $request->request->get('password');
    $passwordConfirm = $request->request->get('passwordConfirm');

    // Vérification du mot de passe

    if ($password !== $passwordConfirm) {
        return $this->render("register.html.twig", ['error' => 'Les mots de passe ne correspondent pas.']);
    }

    // Gestion de l'image
    $image = $request->files->get('avatar');
    if ($image != null) {
        $imageData = file_get_contents($image->getPathname());
        $base64 = base64_encode($imageData);
    } else {
        $base64 = "default.png";
    }

    // Préparation des données à envoyer
    $data = $this->jsonConverter->encodeToJson([
        'username' => $username, 
        'description' => $description,
        'password' => $password,
        'avatar'   => $base64
    ]);

    // Envoi des données au serveur
    $response = $this->apiLinker->postData('/createUser', $data, null);
    $responseObject = json_decode($response);

    // Gestion de la réponse et redirection
    if (isset($responseObject->success)) {
        // Gérer la réussite de l'inscription
        return $this->redirect('/login');
    } else {
        // Gérer l'échec de l'inscription
        return $this->render("register.html.twig", ['error' => 'Erreur lors de l\'inscription.']);
    }
    
}

    

}
