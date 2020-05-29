<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    public function onKernelResponse(ResponseEvent $event)
    {
        // Bonus : On met une variable d'environnement pour afficher ou nous l'annonce de la maintenance
        if ($_ENV['MAINTENANCE_ANNOUNCEMENT_ACTIVE'] == 'true') {
            // $event contient un objet de la classe ResponseEvent
            // Dedans, on a l'objet Response qu'on s'apprête à modifier
            // Il y a aussi l'objet Request, dans lequel on pourrait fouiller pour récupérer des données de la requête
            
            // On récupère l'objet Response
            $response = $event->getResponse();
            
            // Dans cet objet il y a tout le code HTML quî sera envoyé au client.
            // Notre subscriber nous permet d'intercepter le réponse et l'altérer avant de l'envoyer
            // On récupère le contenu HTML
            $content = $response->getContent();
            
            // On remplace la balise <body> par elle-même suivi d'une autre balise avec notre message
            // La valeur de rtour de str_replace est replacée dans $content
            $content = str_replace(
                '<body>',
                '<body><div class="alert alert-danger">'.$_ENV['MAINTENANCE_ANNOUNCEMENT_MESSAGE'].'</div>',
                $content
            );
            
            // On redéfinit le contenu de $response
            $response->setContent($content);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.response' => 'onKernelResponse',
        ];
    }
}
