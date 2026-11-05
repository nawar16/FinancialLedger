<?php
namespace App\Domain\Audit;

use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'audit_logs')]
class AuditLog {
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'bigint')]
    private ?int $id = null;
    public function getId(): ?int { return $this->id; }
}