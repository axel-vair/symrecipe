<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Mark;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use App\Entity\Recipe;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(){
        $this->faker = Factory::create('fr_FR');
    }
    public function load(ObjectManager $manager): void
    {
        // Users
        $users = [];
        for($u = 0; $u < 10; $u++){
            $user = new User();
            $user->setFullName($this->faker->name())
                ->setPseudo(mt_rand(0, 1) === 1 ? $this->faker->firstName() : null)
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword('password');
        $users[] = $user;
            $manager->persist($user);

        }

        // Ingredients

        $ingredients = [];
        for($i = 0; $i<50; $i++){
            $ingredient = new Ingredient();
            $ingredient->setName($this->faker->name)
                ->setPrice(mt_rand(0, 100))
                ->setUser(($users[mt_rand(0, count($users) -1)]));
            $ingredients[] = $ingredient;
            $manager->persist($ingredient);

        }

        // Recipes

        $recipe = [];
        for($i = 0; $i<25; $i++){
            $recipe = new Recipe();
            $recipe->setName($this->faker->word())
                    ->setPrice(mt_rand(0, 1000))
                    ->setTime(mt_rand(0, 1) == 1 ? mt_rand(1, 1440) : null)
                    ->setNbPeople(mt_rand(0, 1) == 1 ? mt_rand(1, 50) : null)
                    ->setDifficulty(mt_rand(0, 1) == 1 ? mt_rand(1, 5) : null)
                    ->setDescription($this->faker->text(300))
                    ->setIsFavorite(mt_rand(0, 1) == 1 ? true : false)
                    ->setIsPublic(mt_rand(0, 1) == 1)
                    ->setUser(($users[mt_rand(0, count($users) -1)]));

                for($x = 0; $x < mt_rand(5, 15); $x++){
                    $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) -1)]);
                }

                $recipes[] = $recipe;
            $manager->persist($recipe);

        }

        // Marks

        foreach ($recipes as $recipe){
            for($r = 0; $r < mt_rand(0,4); $r++){
                $mark = new Mark();
                $mark->setMark(mt_rand(1,5))
                    ->setUser($users[mt_rand(0, count($users) -1)])
                    ->setRecipe($recipe);
                $manager->persist($mark);
            }

        }
        $manager->flush();
    }

}
