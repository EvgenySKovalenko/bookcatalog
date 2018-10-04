<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Entity\Book;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
{ 
    public function removebookfromfavorites(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');        
        $user_loggedin = $this->getUser();
        $user_loggedin_id = $user_loggedin->getId();

        $book_id = $request->query->get('book_id');

        //check that book exists
        $book = $this
            ->getDoctrine()
            ->getRepository(Book::class)
            ->find($book_id); 
        
        if (!$book) {
            $response = new JsonResponse(array('status' => 'Error: no such book.'));
            return $response;
        }   
        
        //check that book is in favorites
        if (!$user_loggedin->isBookInFavorites($book_id)) {
            $response = new JsonResponse(array('status' => 'Error: book is not in favorites.'));
            return $response;            
        }
        
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($user_loggedin_id); 

        //remove book from favorites
        if ($user->removeBookFromFavorites($book_id)) {
            $entityManager->flush();
            $response = new JsonResponse(array('status' => 'ok'));
            return $response;             
        } else {
            $response = new JsonResponse(array('status' => 'Error: remove failed'));
            return $response;              
        }        
    }    
    
    public function addBookToFavorites(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');        
        $user_loggedin = $this->getUser();
        $user_loggedin_id = $user_loggedin->getId();

        $book_id = $request->query->get('book_id');

        //check that book exists
        $book = $this
            ->getDoctrine()
            ->getRepository(Book::class)
            ->find($book_id); 
        
        if (!$book) {
            $response = new JsonResponse(array('status' => 'Error: no such book.'));
            return $response;
        }
        
        //check that book was not added already
        if ($user_loggedin->isBookInFavorites($book_id)) {
            $response = new JsonResponse(array('status' => 'Error: book was added already.'));
            return $response;            
        }
        
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($user_loggedin_id);        
        
        //Add book to favorites
        if ($user->addBookToFavorites($book_id)) {
            $entityManager->flush();
            $response = new JsonResponse(array('status' => 'ok'));
            return $response;             
        } else {
            $response = new JsonResponse(array('status' => 'Error: adding failed'));
            return $response;              
        }        
    }
    
    public function profile($id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user_loggedin = $this->getUser();      
        
        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);    
        
        if (!$user || $user_loggedin->getUsername() !== $user->getUsername()) {
            throw new AccessDeniedException('Access denied.'); 
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
        
        $favorite_book_ids = $user->getFavoritebooks();
        $favorite_books =$this->getDoctrine()
                ->getRepository(Book::class)
                ->findById($favorite_book_ids);

        return $this->render('user/profile.html.twig', [
                'user' => $user,
                'form'  =>  $form->createView(),
                'favorite_books'   =>  $favorite_books
            ]);        
    }
}
