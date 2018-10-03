<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Book;
use App\Entity\Author;
use App\Entity\Genre;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;

class BooksController extends AbstractController
{  
    public function edit($id, Request $request)
    {
        $book = $this
            ->getDoctrine()
            ->getRepository(Book::class)
            ->find($id);
        $form = $this->createFormBuilder($book)
            ->add('name', TextType::class)
            ->add('publishdate', DateType::class)
            ->add('rating', TextType::class)
            ->add('genre', EntityType::class, 
                    ['class' => Genre::class, 'choice_label' => 'name'])
            ->add('author', EntityType::class, 
                    ['class' => Author::class, 'choice_label' => 'name'])
            ->add('save', SubmitType::class, array('label' => 'Update Book'))
            ->getForm(); 
        
        //Form handling
        $form->handleRequest($request); 
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            
            return $this->redirectToRoute('index');
        }

        return $this->render('book/edit.html.twig', [
                'form'  =>  $form->createView()
            ]);
    } 
    
    public function delete($id)
    {
        $book = $this
            ->getDoctrine()
            ->getRepository(Book::class)
            ->find($id);         
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();  
        
        return $this->redirectToRoute('index');        
    }    
}

