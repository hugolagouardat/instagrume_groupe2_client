<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class PageNotFoundController  extends AbstractController {

    public function pageNotFoundAction() {
        return $this->redirect('/');
    }
}
