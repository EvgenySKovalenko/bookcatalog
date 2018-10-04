<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @UniqueEntity(fields="username", message="Username already taken")
 */
class User implements UserInterface {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={"updateusername"})
     */
    private $username;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * The below length depends on the "algorithm" you use for encoding
     * the password, but this works well with bcrypt.
     *
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $favoritebooks = [];

    public function __construct() {
        $this->roles = array('ROLE_USER');
    }

    // other properties and methods
    
    public function isBookInFavorites($book_id)
    {
        return in_array($book_id, $this->favoritebooks);
    }
    
    public function addBookToFavorites($book_id)
    {
        if ($this->isBookInFavorites($book_id)) {
            return false;
        } else {
            $this->favoritebooks[] = $book_id;
            return true;
        }
        
    }    
    
    public function removeBookFromFavorites($book_id)
    {
        if ($this->isBookInFavorites($book_id)) {
            if (($key = array_search($book_id, $this->favoritebooks)) !== false) {
                unset($this->favoritebooks[$key]);
            }            
            return true;
        } else {
            return false;
        }
        
    }      

    public function getId() {
        return $this->id;
    }    
    
    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPlainPassword() {
        return $this->plainPassword;
    }

    public function setPlainPassword($password) {
        $this->plainPassword = $password;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getSalt() {
        // The bcrypt and argon2i algorithms don't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.
        return null;
    }

    public function getRoles() {
        return $this->roles;
    }

    public function eraseCredentials() {
        
    }

    public function getFavoritebooks(): ?array
    {
        return $this->favoritebooks;
    }

    public function setFavoritebooks(?array $favoritebooks): self
    {
        $this->favoritebooks = $favoritebooks;

        return $this;
    }

}
