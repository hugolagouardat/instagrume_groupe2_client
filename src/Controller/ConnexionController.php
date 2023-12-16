<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\JsonConverter;
use App\Service\ApiLinker;

class ConnexionController extends AbstractController {

    private $jsonConverter;
    private $apiLinker;

    public function __construct(ApiLinker $apiLinker, JsonConverter $jsonConverter) {
        $this->apiLinker = $apiLinker;
        $this->jsonConverter = $jsonConverter;
    }

    #[Route('/login', methods: ['GET'])]
    public function displayConnexion(){
        return $this->render("login.html.twig");
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
            } else {
                // Gérez le cas où le token n'est pas récupéré correctement
                return $this->redirect('/login');
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
    public function displayRegister(){
        return $this->render("register.html.twig");
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
    $imageData = file_get_contents($image->getPathname());
    $base64 = base64_encode($imageData);

    // Préparation des données à envoyer
    $data = $this->jsonConverter->encodeToJson([
        'username' => $username, 
        'description' => $description,
        'password' => $password,
        'avatar'   => $base64
    ]);

    // Envoi des données au serveur
    $response = $this->apiLinker->postData('/users', $data, null);
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
