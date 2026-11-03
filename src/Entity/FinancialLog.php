<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'financial_logs')]
class FinancialLog
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint')]
    private int $accountId;

    #[ORM\Column(type: 'decimal', precision: 18, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'string', length: 255)]
    private string $eventType;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        int $accountId,
        string $amount,
        string $eventType,
        ?\DateTimeImmutable $createdAt = null
    ) {
        $this->accountId = $accountId;
        $this->amount = $amount;
        $this->eventType = $eventType;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}