<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use App\Entity\Recipe;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(){
        $this->faker = Factory::create('fr_FR');
    }
    public function load(ObjectManager $manager): void
    {

        for($i = 0; $i<50; $i++){
            $ingredient = new Ingredient();
            $ingredient->setName($this->faker->name)
                ->setPrice(mt_rand(0, 100));
            $manager->persist($ingredient);

        }

        for($i = 0; $i<50; $i++){
            $recipe = new Recipe();
            $recipe->setName($this->faker->name)
                    ->setPrice(mt_rand(0, 1000))
                ->setDescription($this->faker->word(30))
                ->setIngredients($this->faker->word(6));
            $manager->persist($recipe);

        }
        $manager->flush();
    }
}
