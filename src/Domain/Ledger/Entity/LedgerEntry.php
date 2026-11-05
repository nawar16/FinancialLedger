<?php
namespace App\Domain\Ledger;

use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'ledger_entries')]
class LedgerEntry {
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'bigint')]
    private ?int $id = null;
    public function getId(): ?int { return $this->id; }
}