<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Book;
use App\Entity\Genre;
use App\Entity\Author;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends AbstractController
{
    public function index(Request $request)
    {        
        //Create Book form
        $book = new Book();
        $form_create = $this->createFormBuilder($book)
            ->add('name', TextType::class)
            ->add('publishdate', DateType::class)
            ->add('rating', TextType::class)
            ->add('genre', EntityType::class, 
                    ['class' => Genre::class, 'choice_label' => 'name'])
            ->add('author', EntityType::class, 
                    ['class' => Author::class, 'choice_label' => 'name'])
            ->add('save', SubmitType::class, array('label' => 'Add Book'))
            ->getForm(); 
        
        $form_create->handleRequest($request);

        if ($form_create->isSubmitted() && $form_create->isValid()) {           
            $book_new = $form_create->getData();
            $now = new \DateTime();
            $book_new->setDateadded($now);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($book_new);
            $entityManager->flush();
        
            return $this->redirectToRoute('index');
        }
        
        $repository = $this->getDoctrine()->getRepository(Book::class);
        //$books = $repository->findBy([], ['dateadded' => 'DESC'], 10);
        $books = $repository->findBy([], ['dateadded' => 'DESC']);
        
        return $this->render('index.html.twig', [
                'books' => $books,
                'form_create'  =>  $form_create->createView()
            ]);        
    }    
    
    
}

