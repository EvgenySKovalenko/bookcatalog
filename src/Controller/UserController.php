<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{   
    public function profile($id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user_loggedin = $this->getUser();      
        
        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);    
        
        if ($user_loggedin->getUsername() !== $user->getUsername()) {
            die('Access denied.');
        }         
        
        $form = $this->createFormBuilder($user, ['validation_groups' => ['updateusername']])
            ->add('username', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm(); 

        //Form handling
        $form->handleRequest($request); 
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            
            return $this->redirectToRoute('user_profile', ['id' => $id]);
        }        

        return $this->render('user/profile.html.twig', [
                'user' => $user,
                'form'  =>  $form->createView()
            ]);        
    }
}
