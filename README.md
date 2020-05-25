# FAQ O'clock

Clairement Stackoverflow c'est pas si terrible :trollface: alors on s'est dit qu'il fallait réinventer la roue :grinning:

Mais on n'a pas fini le projet… sinon ça serait trop facile.

Voici donc un sacré projet, pas tout à fait terminé. Il a pour but de proposer un site où les utilisateurs peuvent poser des questions et d'autres utilisateurs y répondent. Comme pour Stackoverflow, celui qui a posé la question peut voter pour la meilleure réponse.

Il y a d'autres fonctionnalités mises en place qu'on vous laisse découvrir.

**Avant toutes choses vous devrez donc installer et apprivoiser ce projet.**

> Installez les dépendances, configurez vos variables d'environnement, chargez les fixtures ! (PS : les fixtures ont quelques incohérences mais ce n'est pas bloquant pour le challenge).

# Challenge

On a vu qu'on pouvait attribuer des rôles à des utilisateurs. Grâce aux rôles, on peut définir des droits d'accès à certaines routes. On peut également, dans un contrôleur, n'autoriser l'accès que pour certains rôles. Mais que faire quand on souhaite autoriser l'accès à une route selon l'identité de l'utilisateur ? L'`access_control` ne le permet pas. Comment pourrait-on, par exemple, permettre aux utilisateurs d'éditer leurs questions mais pas celles des autres ?

# Les voters !

Sur cette documentation, vous trouverez tout le nécessaire pour créer des voters : https://symfony.com/doc/current/security/voters.html

**Votre objectif est de créer un voter qui autorise de modifier une question que si l'auteur de la question, un modérateur ou un administrateur tente de le faire.**

Il faudra d'abord créer la route et le contrôleur qui permettent d'éditer une question. Créer la méthode de contrôleur pour modifier une question. Ensuite vous pourrez ajouter le voter et vérifier que l'utilisateur connecté à un droit d'édition sur cette question.

## Bonus

- **Voter** : Modifier le code existant qui permet à un auteur de valider la bonne réponse à sa question. Actuellement c'est en dur dans le contrôleur, peut-on utiliser le voter créé précédemment ?
- **CSS** : Si ça vous dit de mettre un bon coup de frais au design des formulaires, assurez-vous que les formulaires seront générés avec les classes de Bootstrap dans la config `yaml` de Twig !

:tada: