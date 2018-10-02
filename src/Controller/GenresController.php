<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Genre;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class GenresController extends AbstractController
{   
    public function edit($id, Request $request)
    {
        $genre = $this
            ->getDoctrine()
            ->getRepository(Genre::class)
            ->find($id);
        $form = $this->createFormBuilder($genre)
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Update Genre'))
            ->getForm(); 
        
        //Form handling
        $form->handleRequest($request); 
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            
            return $this->redirectToRoute('genres');
        }

        return $this->render('genre/edit.html.twig', [
                'form'  =>  $form->createView()
            ]);
    }   
    
    public function index(Request $request)
    {        
        //Create Genre form
        $genre = new Genre();
        $form_create = $this->createFormBuilder($genre)
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create Genre'))
            ->getForm(); 
        
        $form_create->handleRequest($request);

        if ($form_create->isSubmitted() && $form_create->isValid()) {           
            $genre_new = $form_create->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($genre_new);
            $entityManager->flush();
        
            return $this->redirectToRoute('genres');
        }
        
        $repository = $this->getDoctrine()->getRepository(Genre::class);
        $genres = $repository->findAll();        
        
        return $this->render('genre/index.html.twig', [
                'genres' => $genres,
                'form_create'  =>  $form_create->createView()
            ]);        
    }
}
