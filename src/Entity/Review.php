<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'review', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_review_user_player', columns: ['user_id','player_id'])
])]
#[UniqueEntity(fields: ['user', 'player'], message: 'Vous avez déjà laissé un avis pour ce joueur.')]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'smallint')]
    #[Assert\Range(min:1, max:5, notInRangeMessage: 'La note doit être comprise entre {{ min }} et {{ max }}.')]
    private int $rating; // note sur 5

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(min:2, max:500, minMessage: 'Le commentaire doit contenir au moins {{ limit }} caractères.')]
    private string $comment;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private Player $player;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getRating(): int { return $this->rating; }
    public function setRating(int $rating): self { $this->rating = $rating; return $this; }
    public function getComment(): string { return $this->comment; }
    public function setComment(string $comment): self { $this->comment = $comment; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getUser(): User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getPlayer(): Player { return $this->player; }
    public function setPlayer(?Player $player): self { $this->player = $player; return $this; }

    public function __toString(): string
    {
        return sprintf('Note: %d - %s', $this->rating, mb_substr($this->comment, 0, 30));
    }
}
