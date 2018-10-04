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
    private $recommended_books_count = 5;

    public function book($id)
    {
        $book = $this
            ->getDoctrine()
            ->getRepository(Book::class)
            ->find($id);
            
        if (!$book) {
            throw $this->createNotFoundException('The book does not exist');
        }
               
        $recommended_books = $this->getRecommendedBooks($book, $id);    

        return $this->render('book/book_page.html.twig', [
                'book'  =>  $book,
                'recommended' => $recommended_books
            ]);        
    }    
    
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
    
    private function getRecommendedBooks($book, $book_id)
    {
        //get recommended books of this genre
        $genre_id = $book->getGenre()->getId();
        $author_id = $book->getAuthor()->getId();        
        $entityManager = $this->getDoctrine()->getManager();
        $conn = $entityManager->getConnection();
        $sql = 'SELECT id FROM book 
                WHERE id != :id AND genre_id = :genre_id
                ORDER BY RAND() LIMIT ' . $this->recommended_books_count;
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'id' => $book_id, 
            'genre_id' => $genre_id, 
        ]);         
        $ids_genre = $stmt->fetchAll(\PDO::FETCH_COLUMN);        
        $ids_genre_count = count($ids_genre);
        
        if ($ids_genre_count < $this->recommended_books_count) {
            //get other books of this author
            $ids_genre_str = implode(',', $ids_genre);            
            if ($ids_genre_count > 0) {
                $not_in_genre = ' AND id NOT IN ('.$ids_genre_str.') ';
            } else {
                $not_in_genre = '';
            }
            $limit = $this->recommended_books_count - $ids_genre_count;
            $sql = 'SELECT id FROM book
                    WHERE id !=:id '.$not_in_genre.' 
                        AND author_id = :author_id
                    ORDER BY RAND() LIMIT ' . $limit;           
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'id' => $book_id, 
                'author_id' => $author_id, 
            ]); 
            $ids_author = $stmt->fetchAll(\PDO::FETCH_COLUMN);        
            $ids_author_count = count($ids_author);              
            $ids_genre_author_count = $ids_genre_count + $ids_author_count;
            
            if ($ids_genre_author_count < $this->recommended_books_count) {
                //get other books
                $ids_genre_author = array_merge($ids_genre, $ids_author);
                $ids_genre_author_str = implode(',', $ids_genre_author);
                if ($ids_genre_author_count > 0) {
                    $not_in_genre_author = ' AND id NOT IN ('.$ids_genre_author_str.') ';
                } else {
                    $not_in_genre_author = '';
                } 
                $limit = $this->recommended_books_count - $ids_genre_author_count;
                $sql = 'SELECT id FROM book
                        WHERE id !=:id '.$not_in_genre_author.' 
                        ORDER BY RAND() LIMIT ' . $limit; 
                $stmt = $conn->prepare($sql);
                $stmt->execute(['id' => $book_id]);  
                $ids_others = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            }
        } 
        
        //get books
        $rep = $this->getDoctrine()->getRepository(Book::class);
        $books_genre = !empty($ids_genre) ? $rep->findById($ids_genre) : [];
        $books_author = !empty($ids_author) ? $rep->findById($ids_author) : [];
        $books_others = !empty($ids_others) ? $rep->findById($ids_others) : [];
        
        return [
            'books_genre'  => $books_genre,
            'books_author'  => $books_author,
            'books_others'  => $books_others,
        ];
    }    
}

