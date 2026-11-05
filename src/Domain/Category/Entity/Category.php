<?php
namespace App\Domain\Category;

use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'categories')]
class Category {
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'bigint')]
    private ?int $id = null;
    public function getId(): ?int { return $this->id; }
}