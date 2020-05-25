<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // On crée une instance de Faker en français
        $generator = Faker\Factory::create('fr_FR');

        // On passe le Manager de Doctrine à Faker !
        $populator = new Faker\ORM\Doctrine\Populator($generator, $manager);

        // Roles
        $roles = [
            'admin' => 'Administrateur',
            'moderator' => 'Modérateur',
            'user' => 'Utilisateur',
        ];

        // Users : seront créés en dur pour pouvoir les manipuler en attendant le module de sécurité
        $userGroup = array(
            'admin' => ['claire', 'jc'],
            'moderator' => ['micheline', 'jeanette', 'victoria'],
            'user' => ['gertrude', 'roland', 'marc', 'alfred', 'michel'],
        );
        $usersEntities = array();

        foreach($userGroup as $roleGroup => $users) {
            // Role
            $role = new Role();
            $role->setRoleString('ROLE_'.mb_strtoupper($roleGroup));
            $role->setName($roles[$roleGroup]);
            $manager->persist($role);

            print 'Adding role '.$role->getName().PHP_EOL;
            
            foreach($users as $u) {
                // New user based on list
                $user = new User();
                $user->setUsername(\ucfirst($u));
                $user->setPassword($this->encoder->encodePassword($user, $u)); // Le mot de passe est le nom de l'utilisateur
                $user->setEmail($u.'@faq.oclock.io');
                $user->setRole($role);
                // Add it to the list of entities
                $usersEntities[] = $user;
                // Persist
                $manager->persist($user);

                print 'Adding user '.$user->getUsername().PHP_EOL;
            }
        }

        // Tags
        $populator->addEntity('App\Entity\Tag', 10, [
            'name' => function () use ($generator) {
                return $generator->unique()->word();
            },
        ]);

        // Questions
        $populator->addEntity('App\Entity\Question', 30, [
            'title' => function () use ($generator) {
                return (rtrim($generator->unique()->sentence($nbWords = 9, $variableNbWords = true), '.') . ' ?');
            },
            'body' => function () use ($generator) {
                return $generator->unique()->paragraph($nbSentences = 6, $variableNbSentences = true);
            },
            'createdAt' => function () use ($generator) {
                return $generator->unique()->dateTime($max = 'now', $timezone = null);
            },
            'votes' => 0,
            'user' => function () use ($generator, $usersEntities) {
                return $usersEntities[$generator->numberBetween($min = 0, $max = (count($usersEntities)-1))];
            },
        ]);

        // Answers
        $populator->addEntity('App\Entity\Answer', 50, [
            'body' => function () use ($generator) {
                return $generator->unique()->paragraph($nbSentences = 3, $variableNbSentences = true);
            },
            'createdAt' => function () use ($generator) {
                return $generator->unique()->dateTime($max = 'now', $timezone = null);
            },
            'votes' => 0,
            'user' => function () use ($generator, $usersEntities) {
                return $usersEntities[$generator->numberBetween($min = 0, $max = (count($usersEntities)-1))];
            },
        ]);
        // Exécution et récupération dse entités ajoutées par Faker
        $insertedEntities = $populator->execute();

        // On doit ajouter manuellement des données pour les ManyToMany
        // Depuis les données insérées :
        $tags = $insertedEntities['App\Entity\Tag'];
        $questions = $insertedEntities['App\Entity\Question'];

        // Tags sur questions
        foreach ($questions as $question) {
            // On mélange les tags et on en récupère 1 à 3 au hasard
            shuffle($tags);
            $tagCount = mt_rand(1, 3);
            for ($i = 1; $i <= $tagCount; $i++) {
                $question->addTag($tags[$i]);
            }
        }
        // Flush
        $manager->flush();
    }
}
