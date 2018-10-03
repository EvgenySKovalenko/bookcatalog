<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Author;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class AuthorsController extends AbstractController
{  
    public function edit($id, Request $request)
    {
        $author = $this
            ->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);
        $form = $this->createFormBuilder($author)
            ->add('name', TextType::class)
            ->add('birthdate', BirthdayType::class)
            ->add('gender', ChoiceType::class, 
                    ['choices' => ['male' => 'male', 'female' => 'female']])                
            ->add('save', SubmitType::class, array('label' => 'Update Author'))
            ->getForm(); 
        
        //Form handling
        $form->handleRequest($request); 
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            
            return $this->redirectToRoute('authors');
        }

        return $this->render('author/edit.html.twig', [
                'form'  =>  $form->createView()
            ]);
    }  
    
    public function delete($id)
    {
        $author = $this
            ->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);         
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();  
        
        return $this->redirectToRoute('authors');        
    }    
    
    public function index(Request $request)
    {        
        //Create Author form
        $author = new Author();
        $form_create = $this->createFormBuilder($author)
            ->add('name', TextType::class)
            ->add('birthdate', BirthdayType::class)
            ->add('gender', ChoiceType::class, 
                    ['choices' => ['male' => 'male', 'female' => 'female']])
            ->add('save', SubmitType::class, array('label' => 'Add Author'))
            ->getForm();
        
        $form_create->handleRequest($request);

        if ($form_create->isSubmitted() && $form_create->isValid()) {           
            $author_new = $form_create->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($author_new);
            $entityManager->flush();
        
            return $this->redirectToRoute('authors');
        }  
        
        $repository = $this->getDoctrine()->getRepository(Author::class);
        $authors = $repository->findAll();         
        
        return $this->render('author/index.html.twig', [
                'authors' => $authors,
                'form_create'  =>  $form_create->createView()
            ]);        
    }
}
