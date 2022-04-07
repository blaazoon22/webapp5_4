<?php

namespace App\Entity;

use App\Repository\PostCodeDataRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostCodeDataRepository::class)]
class PostCodeData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;


    #[ORM\Column(type: 'json')]
    private $postcodejson = [];

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getPostcodejson(): ?array
    {
        return $this->postcodejson;
    }

    public function setPostcodejson(array $postcodejson): self
    {
        $this->postcodejson = $postcodejson;

        return $this;
    }
}
