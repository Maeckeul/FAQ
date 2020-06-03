<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QuestionControllerTest extends WebTestCase
{
    public function testAnonymous()
    {
        $client = static::createClient();
        // On vérifie que la page d'accueil est accessible à un utilisateur anonyme
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        // On vérifie que la page de la question 1 est accessible à un utilisateur anonyme
        $crawler = $client->request('GET', '/question/1');
        $this->assertResponseIsSuccessful();
    }

    public function testUser()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'alfred',
            'PHP_AUTH_PW'   => 'alfred',
        ]);

        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        // On vérfiei la présence des deux boutons et leurs contenus
        $this->assertSelectorTextContains('nav .btn-danger', 'Poser une question');
        $this->assertSelectorTextContains('nav .btn-primary', 'Mon compte');
    }

    public function testPageAdmin()
    {
        // On teste différents résultats, selon le rôle sur la page /admin/user

        // D'abord en anonyme
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/user');
        // On vérifie qu'on a une 302
        $this->assertResponseStatusCodeSame(302);
        // On pourrait aussi vérifier qu'on a une redirection mais ici
        // on teste pas le code 302 mais tous les code de status de réponse
        // correspondant à des redirections : 3XX
        $this->assertResponseRedirects();

        // Testons avec un ROLE_USER
        $crawler = $client->request('GET', '/admin/user', [], [], [
            'PHP_AUTH_USER' => 'alfred',
            'PHP_AUTH_PW'   => 'alfred',
        ]);
        // On vérifie que l'accès est interdit
        $this->assertResponseStatusCodeSame(403);

        // Testons avec un ROLE_MODERATOR
        $crawler = $client->request('GET', '/admin/user', [], [], [
            'PHP_AUTH_USER' => 'micheline',
            'PHP_AUTH_PW'   => 'micheline',
        ]);
        $this->assertResponseIsSuccessful();

        // Testons avec un ROLE_ADMIN
        $crawler = $client->request('GET', '/admin/user', [], [], [
            'PHP_AUTH_USER' => 'jc',
            'PHP_AUTH_PW'   => 'jc',
        ]);
        $this->assertResponseIsSuccessful();

    }
}
