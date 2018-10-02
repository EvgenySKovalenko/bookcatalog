<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class IndexController extends AbstractController
{
    public function index(AuthorizationCheckerInterface $authChecker)
    {
        /*
        if ($authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            die('AUTHENTICATED');
        } else {
            die('NOT AUTHENTICATED');
        }
         */
        
        return $this->render('index.html.twig', []); 
    }    
}

