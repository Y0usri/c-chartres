<?php

namespace App\Tests\Entity;

use App\Entity\Review;
use App\Entity\Player;
use App\Entity\Category;
use App\Entity\Level;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReviewValidationTest extends KernelTestCase
{
    private function getValidator(): ValidatorInterface
    {
        self::bootKernel();
        return static::getContainer()->get(ValidatorInterface::class);
    }

    public function testInvalidRating(): void
    {
        $validator = $this->getValidator();
        $cat = (new Category())->setName('Cat '.uniqid());
        $lvl = (new Level())->setName('Lvl '.uniqid());
        $player = (new Player())
            ->setFirstName('Test')
            ->setLastName('Player')
            ->setBirthDate(new \DateTimeImmutable('2001-01-01'))
            ->setCategory($cat)
            ->setLevel($lvl);
        $user = (new User())
            ->setEmail(uniqid().'@mail.test')
            ->setFirstName('First')
            ->setLastName('Last')
            ->setPassword('hash');
        $review = (new Review())
            ->setPlayer($player)
            ->setUser($user)
            ->setRating(10) // invalide
            ->setComment('Ok')
            ->setCreatedAt(new \DateTimeImmutable());
        $violations = $validator->validate($review);
        $this->assertGreaterThan(0, count($violations));
    }
}
