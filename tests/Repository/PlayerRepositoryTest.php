<?php

namespace App\Tests\Repository;

use App\Entity\Player;
use App\Entity\Review;
use App\Entity\Category;
use App\Entity\Level;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlayerRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAverageRatings(): void
    {
        $cat = (new Category())->setName('TestCat '.uniqid());
        $lvl = (new Level())->setName('TestLvl '.uniqid());
        $player = (new Player())
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setBirthDate(new \DateTimeImmutable('2000-01-01'))
            ->setCategory($cat)
            ->setLevel($lvl);
        $user = (new User())
            ->setEmail(uniqid('u').'@ex.com')
            ->setFirstName('U')
            ->setLastName('Ser')
            ->setPassword('hash');
        $review = (new Review())
            ->setPlayer($player)
            ->setUser($user)
            ->setRating(5)
            ->setComment('Super')
            ->setCreatedAt(new \DateTimeImmutable());
        $this->em->persist($cat); $this->em->persist($lvl); $this->em->persist($player); $this->em->persist($user); $this->em->persist($review);
        $this->em->flush();

        $repo = $this->em->getRepository(Player::class);
        $avg = $repo->getAverageRatingForPlayer($player->getId());
        $this->assertSame(5.0, $avg);
    }
}
