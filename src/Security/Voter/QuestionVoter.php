<?php

namespace App\Security\Voter;

use App\Entity\Question;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class QuestionVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // https://symfony.com/doc/current/security/voters.html
        // Lorsqu'on exécute denyAccessUnlessGranted() dans une contrôleur
        // Tous les voters sont instanciés, et leurs méthodes supports() sont toutes exécutées
        // Si supports() retourne TRUE, la méthode voteOnAttribute() est exécutée

        // $attribute est une chaine de caractères,
        // on teste si elle est présente dans une liste de droits gérés par ce Voter
        // $subject est l'objet concerné, on attend un objet de la classe Question
        return in_array($attribute, ['edit', 'view'])
            && $subject instanceof Question;
    }

    protected function voteOnAttribute($attribute, $question, TokenInterface $token)
    {
        // voteOnAttribute() doit retourner true si l'utilisateur a le droit $attribute
        // et false sinon

        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'edit':
                // Si l'utilisateur connecté est l'auteur de la question, on autorise sa modification
                if ($user == $question->getUser()) {
                    return true;
                }
                // Si l'utilisateur a un ROLE_ADMIN ou un ROLE_MODERATOR, on retourne true
                if (in_array($user->getRole()->getRoleString(), ['ROLE_ADMIN', 'ROLE_MODERATOR'])) {
                    return true;
                }

                // Voici des variantes du précédent if :

                // On peut obtenir le rôle dûne autre façon, tout en faisant le même test
                // if (in_array($user->getRoles()[0], ['ROLE_ADMIN', 'ROLE_MODERATOR'])) {
                //     return true;
                // }

                // On peut également retourner le résultat de in_array
                // Avec cette variante on retourne forcément un booléen, qui correspond au résultat de in_array()
                // return in_array($user->getRole()->getRoleString(), ['ROLE_ADMIN', 'ROLE_MODERATOR']);

                // Dans la même idée que la variante précédente, on pourrait faire un seul return
                // avec tous nos tests dedans (un peu comme dans la méthode supports())
                // return (
                //     $user == $question->getUser()
                //     || in_array($user->getRole()->getRoleString(), ['ROLE_ADMIN', 'ROLE_MODERATOR'])
                // );

                // Le break permet de sortir du switch, sans lui le code dans le case 'view'
                // sera exécuté également pour case 'edit'
                break;
            case 'view':
                // On n'a rien à tester ici mais on laisse ce code présent pour
                // montrer comment on pourrait tester plusieurs droits dans le même Voter
                // return true or false
                break;
        }

        // Ceci est une sécurité, un moyen de s'assurer que voteOnAttribute retourne au moins un booléen
        // Si rien au-dessus n'a retourné quoique ce soit, on retourne false
        return false;
    }
}
