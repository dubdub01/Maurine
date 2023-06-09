<?php 

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class UserImgModify{

    #[Assert\Image(mimeTypes:["image/png","image/jpeg","image/jpg","image/gif"], mimeTypesMessage:"Vous devez upload un fichier jpg, jpeg, png ou gif")]
    #[Assert\File(maxSize:"1024k", maxSizeMessage:"La taille du fichier est trop grande")]
    private ?string $newAvatar = null;

    public function getNewAvatar(): ?string
    {
        return $this->newAvatar;
    }

    public function setNewAvatar(?string $newAvatar): self
    {
        $this->newAvatar = $newAvatar;

        return $this;
    }
}
