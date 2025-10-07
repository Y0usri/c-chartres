<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min:2, max:100)]
    private string $lastName;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min:2, max:100)]
    private string $firstName;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank]
    #[Assert\LessThan('today')]
    private \DateTimeInterface $birthDate;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoFilename = null; // chemin/nom du fichier

    #[ORM\ManyToOne(inversedBy: 'players')]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\ManyToOne(inversedBy: 'players')]
    #[ORM\JoinColumn(nullable: false)]
    private Level $level;

    /** @var Collection<int, Review> */
    #[ORM\OneToMany(mappedBy: 'player', targetEntity: Review::class, cascade: ['remove'])]
    private Collection $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getLastName(): string { return $this->lastName; }
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }
    public function getFirstName(): string { return $this->firstName; }
    public function setFirstName(string $firstName): self { $this->firstName = $firstName; return $this; }
    public function getBirthDate(): \DateTimeInterface { return $this->birthDate; }
    public function setBirthDate(\DateTimeInterface $birthDate): self { $this->birthDate = $birthDate; return $this; }
    public function getPhotoFilename(): ?string { return $this->photoFilename; }
    public function setPhotoFilename(?string $photoFilename): self { $this->photoFilename = $photoFilename; return $this; }
    public function getCategory(): Category { return $this->category; }
    public function setCategory(Category $category): self { $this->category = $category; return $this; }
    public function getLevel(): Level { return $this->level; }
    public function setLevel(Level $level): self { $this->level = $level; return $this; }

    /** @return Collection<int, Review> */
    public function getReviews(): Collection { return $this->reviews; }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setPlayer($this);
        }
        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getPlayer() === $this) {
                $review->setPlayer(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
