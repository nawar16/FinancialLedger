<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User {
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'bigint')]
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $username;
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;
    #[ORM\Column(type: 'string', length: 255)]
    private string $password;
    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'active';
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;
    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function setUsername(string $v): void { $this->username = $v; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $v): void { $this->email = $v; }
    public function getPassword(): string { return $this->password; }
    public function setPassword(string $v): void { $this->password = $v; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $v): void { $this->status = $v; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
