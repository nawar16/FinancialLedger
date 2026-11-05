<?php
namespace App\Domain\Transaction;

use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'transactions')]
class Transaction {
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'bigint')]
    private ?int $id = null;
    public function getId(): ?int { return $this->id; }
}