<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(columns: ['user_id', 'visited_at'])]
class UserPageVisit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageVisits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(name: 'page_url', length: 255)]
    private ?string $pageUrl = null;

    #[ORM\Column(name: 'page_title', length: 255, nullable: true)]
    private ?string $pageTitle = null;

    #[ORM\Column(name: 'visited_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $visitedAt = null;

    #[ORM\Column(name: 'user_agent', length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(name: 'ip_address', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(name: 'time_spent', nullable: true)]
    private ?int $timeSpent = null;

    public function __construct()
    {
        $this->visitedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPageUrl(): ?string
    {
        return $this->pageUrl;
    }

    public function setPageUrl(string $pageUrl): static
    {
        $this->pageUrl = $pageUrl;
        return $this;
    }

    public function getPageTitle(): ?string
    {
        return $this->pageTitle;
    }

    public function setPageTitle(?string $pageTitle): static
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    public function getVisitedAt(): ?\DateTimeInterface
    {
        return $this->visitedAt;
    }

    public function setVisitedAt(\DateTimeInterface $visitedAt): static
    {
        $this->visitedAt = $visitedAt;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(?int $timeSpent): static
    {
        $this->timeSpent = $timeSpent;
        return $this;
    }

    public function __toString(): string
    {
        return $this->pageTitle ?? $this->pageUrl ?? '';
    }
}
