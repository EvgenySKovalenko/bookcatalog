<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Genre;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
    
    public function delete($id)
    {
        $genre = $this
            ->getDoctrine()
            ->getRepository(Genre::class)
            ->find($id);
        
        $genre_books = $genre->getBooks();

        if (count($genre_books)) {
            $this->addFlash(
                'error',
                'Error: Genre can not be deleted, because there are books of this genre'
            );
            
            return $this->redirectToRoute('genres');           
        }
               
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($genre);
        $entityManager->flush();  
        
        return $this->redirectToRoute('genres');        
    }    
    
    public function index($id, Request $request)
    {        
        //Process Genre filter
        $genre_id = $request->get('genre_id');
        if ($genre_id) {
            return $this->redirectToRoute('genres', ['id' => $genre_id]);
        }
        
        //Create Genre form
        $genre_empty = new Genre();
        $form_create = $this->createFormBuilder($genre_empty)
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create Genre'))
            ->getForm(); 
        
        //Process Create Genre form
        $form_create->handleRequest($request);

        if ($form_create->isSubmitted() && $form_create->isValid()) {           
            $genre_new = $form_create->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($genre_new);
            $entityManager->flush();
        
            return $this->redirectToRoute('genres', ['id' => $id]);
        }
        
        $repository = $this->getDoctrine()->getRepository(Genre::class);        
        $genres = $repository->findAll(); 

        $genre_selected = $repository->find($id);
        
        return $this->render('genre/index.html.twig', [
                'genres' => $genres,
                'form_create'  =>  $form_create->createView(),
                'genre_selected' => $genre_selected,
                'genre_selected_id' => $id
            ]);        
    }
}
