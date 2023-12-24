<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Security;

use App\Service\JsonConverter;
use App\Service\ApiLinker;
use DateTime;

class ToolFunctions
{
    private $apiLinker;

    public function __construct(ApiLinker $apiLinker)
    {
        $this->apiLinker = $apiLinker;
    }

    //Retourne le payload d'un token
    public function getPayload($token)
    {
        if ($token !== null) {
            $tokenParts = explode('.', $token);
            $tokenPayload = base64_decode($tokenParts[1]); // Décodage de la partie Payload

            $payloadData = json_decode($tokenPayload);
            return $payloadData; //Username, password, rôle, expiration(exp)
        } else {
            return null;
        }
    }

    //Vérifie si le token est expiré
    public function isTokenExpirated($payloadData)
    {
        if ($payloadData) {
            $expiration = $payloadData->exp;

            // Créer un objet DateTime à partir du timestamp en secondes
            $expdate = new DateTime("@$expiration");
            $actualdate = new DateTime();

            // Formater la date d'expiration selon vos besoins
            $dateExpiration = $expdate->format('Y-m-d H:i:s');
            $dateActuelle = $actualdate->format('Y-m-d H:i:s');

            // Retourner true si expiré
            if ($dateActuelle >= $dateExpiration) {
                return true;
            }
        }
        return false;
    }

    //Prend en paramètre un username et retourne l'id correspondant
    public function getIdByUsername($username)
    {
        $data = [
            "username" => $username
        ];

        // Assurez-vous que le service renvoie une chaîne JSON
        $jsonResponse = $this->apiLinker->getData("/getIdByUsername/" . $username, null);

        // Décoder la chaîne JSON en tableau PHP
        $responseData = json_decode($jsonResponse, true);

        // Vérifiez si la clé "id" existe dans le tableau
        if (isset($responseData['id'])) {
            return $responseData['id'];
        }

        // Si la clé "id" n'existe pas, retournez null ou une valeur par défaut selon vos besoins
        return null;
    }

    public function getPhotoById($photoId) {
        // Assurez-vous que le service renvoie une chaîne JSON
        $jsonResponse = $this->apiLinker->getData("/photos/" . $photoId, null);
    
        // Décoder la chaîne JSON en tableau PHP
        $responseData = json_decode($jsonResponse, true);
    
        // Vérifiez si les données de la photo existent dans le tableau
        if (!empty($responseData)) {
            return $responseData;
        }
    
        // Si les données de la photo n'existent pas, retournez null ou une valeur par défaut selon vos besoins
        return null;
    }
    
}
