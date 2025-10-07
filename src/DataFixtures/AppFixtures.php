<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Level;
use App\Entity\Player;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        // Catégories sportives (réutilisation si existantes)
        $categoryNames = ['Football', 'Handball', 'Volleyball', 'Basketball'];
        $categories = [];
        $categoryRepo = $manager->getRepository(Category::class);
        foreach ($categoryNames as $catName) {
            $existing = $categoryRepo->findOneBy(['name' => $catName]);
            if ($existing) {
                $categories[$catName] = $existing;
            } else {
                $cat = (new Category())->setName($catName);
                $manager->persist($cat);
                $categories[$catName] = $cat;
            }
        }

        // Niveaux (réutilisation si existants)
        $levelNames = ['Ligue 1', 'Ligue 2', 'National', 'Régional'];
        $levels = [];
        $levelRepo = $manager->getRepository(Level::class);
        foreach ($levelNames as $lvlName) {
            $existingLvl = $levelRepo->findOneBy(['name' => $lvlName]);
            if ($existingLvl) {
                $levels[$lvlName] = $existingLvl;
            } else {
                $lvl = (new Level())->setName($lvlName);
                $manager->persist($lvl);
                $levels[$lvlName] = $lvl;
            }
        }

        // Joueurs connus (nom, prénom, naissance, catégorie, niveau)
        $playersData = [
            ['Mbappé', 'Kylian', '1998-12-20', 'Football', 'Ligue 1'],
            ['Messi', 'Lionel', '1987-06-24', 'Football', 'Ligue 1'],
            ['Ndiaye', 'Orlane', '1994-06-18', 'Handball', 'National'],
            ['Grbic', 'Tijana', '1997-10-13', 'Volleyball', 'National'],
            ['Curry', 'Stephen', '1988-03-14', 'Basketball', 'Ligue 1'],
            ['James', 'LeBron', '1984-12-30', 'Basketball', 'Ligue 1'],
            // Ajout de 30 joueurs multi-sports
            ['Ronaldo', 'Cristiano', '1985-02-05', 'Football', 'Ligue 1'],
            ['Haaland', 'Erling', '2000-07-21', 'Football', 'Ligue 1'],
            ['De Bruyne', 'Kevin', '1991-06-28', 'Football', 'Ligue 1'],
            ['Lewandowski', 'Robert', '1988-08-21', 'Football', 'Ligue 1'],
            ['Modric', 'Luka', '1985-09-09', 'Football', 'Ligue 1'],
            ['Benzema', 'Karim', '1987-12-19', 'Football', 'Ligue 1'],
            ['Kanté', 'NGolo', '1991-03-29', 'Football', 'Ligue 1'],
            ['Salah', 'Mohamed', '1992-06-15', 'Football', 'Ligue 1'],
            ['Neymar', 'Junior', '1992-02-05', 'Football', 'Ligue 1'],
            ['Xavi', 'Hernandez', '1980-01-25', 'Football', 'Ligue 2'],
            ['Iniesta', 'Andres', '1984-05-11', 'Football', 'Ligue 2'],
            ['Gasol', 'Pau', '1980-07-06', 'Basketball', 'Ligue 1'],
            ['Durant', 'Kevin', '1988-09-29', 'Basketball', 'Ligue 1'],
            ['Doncic', 'Luka', '1999-02-28', 'Basketball', 'Ligue 1'],
            ['Jokic', 'Nikola', '1995-02-19', 'Basketball', 'Ligue 1'],
            ['Embiid', 'Joel', '1994-03-16', 'Basketball', 'Ligue 1'],
            ['Parker', 'Tony', '1982-05-17', 'Basketball', 'Ligue 2'],
            ['Ginobili', 'Manu', '1977-07-28', 'Basketball', 'Ligue 2'],
            ['Campazzo', 'Facundo', '1991-03-23', 'Basketball', 'National'],
            ['Zhu', 'Ting', '1994-11-29', 'Volleyball', 'Ligue 1'],
            ['Boskovic', 'Tijana', '1997-03-08', 'Volleyball', 'Ligue 1'],
            ['Rasic', 'Milena', '1990-10-25', 'Volleyball', 'Ligue 2'],
            ['Egonu', 'Paola', '1998-12-18', 'Volleyball', 'Ligue 1'],
            ['Karabatic', 'Nikola', '1984-04-11', 'Handball', 'Ligue 1'],
            ['Omeyer', 'Thierry', '1976-11-02', 'Handball', 'Ligue 1'],
            ['Hansen', 'Mikkel', '1987-10-22', 'Handball', 'Ligue 1'],
            ['Groot', 'Nycke', '1988-05-04', 'Handball', 'Ligue 2'],
            ['Neagu', 'Cristina', '1988-08-26', 'Handball', 'National'],
            ['Abalo', 'Luc', '1984-09-06', 'Handball', 'Ligue 2'],
            ['Bohme', 'Anna', '1993-07-14', 'Handball', 'National'],
        ];

        $players = [];
        foreach ($playersData as [$last, $first, $birth, $cat, $lvl]) {
            $p = new Player();
            $p->setLastName($last)
              ->setFirstName($first)
              ->setBirthDate(new \DateTimeImmutable($birth))
              ->setCategory($categories[$cat])
              ->setLevel($levels[$lvl]);
            $manager->persist($p);
            $players[] = $p;
        }

        // Utilisateur admin + user simple
        // Utilisateurs (réutilisation si existants)
        $userRepo = $manager->getRepository(User::class);
        $admin = $userRepo->findOneBy(['email' => 'admin@example.com']);
        if (!$admin) {
            $admin = (new User())
                ->setEmail('admin@example.com')
                ->setFirstName('Alice')
                ->setLastName('Dupont')
                ->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
            $manager->persist($admin);
        }

        $user = $userRepo->findOneBy(['email' => 'user@example.com']);
        if (!$user) {
            $user = (new User())
                ->setEmail('user@example.com')
                ->setFirstName('Bob')
                ->setLastName('Martin')
                ->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
            $manager->persist($user);
        }

        // Quelques avis
        $sampleComments = [
            'Excellent joueur',
            'Très bon potentiel',
            'Doit travailler la régularité',
            'Performance solide',
        ];
        $i = 0;
        foreach ($players as $pl) {
            $review = new Review();
            $review->setPlayer($pl)
                ->setUser(($i % 2 === 0) ? $admin : $user)
                ->setRating(rand(3, 5))
                ->setComment($sampleComments[$i % count($sampleComments)])
                ->setCreatedAt(new \DateTimeImmutable(sprintf('2025-10-%02d', 1 + ($i % 28))));
            $manager->persist($review);
            $i++;
        }

        $manager->flush();
    }
}
