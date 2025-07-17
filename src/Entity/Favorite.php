<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
#[ORM\Table(name: 'favorites')]
#[ORM\UniqueConstraint(name: 'user_page_unique', columns: ['user_id', 'page_id'])]

class Favorite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['favorite:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'favorites')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['favorite:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'favorites')]
    #[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['favorite:read'])]
    private ?Page $page = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['favorite:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[Groups(['favorite:read'])]
    public function getSlug(): ?string
    {
        return $this->page?->getSlug();
    }

    #[Groups(['favorite:read'])]
    public function getPageContent(): ?PageContent
    {
        $pageContents = $this->page?->getPageContents();
        return $pageContents && $pageContents->count() > 0 ? $pageContents->first() : null;
    }

    #[Groups(['favorite:read'])]
    public function getTitle(): ?string
    {
        return $this->getPageContent()?->getTitle();
    }

    #[Groups(['favorite:read'])]
    public function getCategory(): ?Category
    {
        return $this->getPageContent()?->getCategory();
    }
}