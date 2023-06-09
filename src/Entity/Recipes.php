<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\RecipesRepository;
use phpDocumentor\Reflection\PseudoTypes\Numeric_;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: RecipesRepository::class)]
class Recipes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Vous devez renseigner votre prénom")]
    #[Assert\Length(min: 3, max:100, minMessage:"Le titre de la recette doit faire au moins 3 caractères", maxMessage:"Le titre de la recette ne peut excéder 100 caractères")]
    private ?string $title = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Vous devez renseigner le temps de préparation")]
    #[Assert\Type(type:"numeric", message:"La valeur doit être numérique")]
    private ?int $time = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Vous devez renseigner le niveau de difficulté de la préparation")]
    #[Assert\Choice(['Facile', 'Moyen', 'Difficile'])]
    private ?string $level = null;

    #[ORM\Column(nullable: true)]
    private ?int $note = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Vous devez renseigner le budget de la préparation")]
    #[Assert\Choice(['Faible', 'Moyen', 'Coûteux'])]
    private ?string $budget = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Vous devez renseigner le nombre de portions de la préparation")]
    #[Assert\Type(type:"numeric", message:"La valeur doit être numérique")]
    private ?int $portions = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Image(mimeTypes:["image/png","image/jpeg","image/jpg","image/gif"], mimeTypesMessage:"Vous devez upload un fichier jpg, jpeg, png ou gif")]
    #[Assert\File(maxSize:"1024k", maxSizeMessage:"La taille du fichier est trop grande")]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Vous devez renseigner les ingrédients")]
    private ?string $ingredient = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Vous devez renseigner les étapes de la préparation")]
    private ?string $steps = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Vous devez renseigner la catégorie de la préparation")]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'recettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $idUser = null;

    #[ORM\OneToMany(mappedBy: 'idRecipe', targetEntity: Comments::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'recipe', targetEntity: Like::class, orphanRemoval: true, cascade:["persist"])]
    private Collection $likes;

    #[ORM\OneToMany(mappedBy: 'recipe', targetEntity: Galery::class, orphanRemoval: true)]
    private Collection $galeries;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->galeries = new ArrayCollection();
    }



     /**
     * Initialisation automatique du slug 
     *
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initializeSlug():void{
        if (empty($this->slug)){
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title.''.rand());
        }
    }

    /**
     * Permet de récup la note d'une recette
     *
     * @return integer
     */
    public function getAvgRatings(): int 
    {
       
        $sum = array_reduce($this->comments->toArray(), function($total, $comment){
            return $total + $comment->getNote();
        },0);

        // faire la division pour avoir la moyenne 
        if(count($this->comments) > 0) return $moyennne = round($sum / count($this->comments));
        return 0;
    }

    


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(int $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(string $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    public function getPortions(): ?int
    {
        return $this->portions;
    }

    public function setPortions(int $portions): self
    {
        $this->portions = $portions;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getIngredient(): ?string
    {
        return $this->ingredient;
    }

    public function setIngredient(string $ingredient): self
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function getSteps(): ?string
    {
        return $this->steps;
    }

    public function setSteps(string $steps): self
    {
        $this->steps = $steps;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->idUser;
    }

    public function setIdUser(?User $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setIdRecipe($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getIdRecipe() === $this) {
                $comment->setIdRecipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setRecipe($this);
        }

        return $this;
    }

    public function removeLike(Like $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getRecipe() === $this) {
                $like->setRecipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Galery>
     */
    public function getGaleries(): Collection
    {
        return $this->galeries;
    }

    public function addGalery(Galery $galery): self
    {
        if (!$this->galeries->contains($galery)) {
            $this->galeries->add($galery);
            $galery->setRecipe($this);
        }

        return $this;
    }

    public function removeGalery(Galery $galery): self
    {
        if ($this->galeries->removeElement($galery)) {
            // set the owning side to null (unless already changed)
            if ($galery->getRecipe() === $this) {
                $galery->setRecipe(null);
            }
        }

        return $this;
    }

   
}
