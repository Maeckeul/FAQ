<?php

namespace App\Controller;

use App\Entity\Answer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AnswerController extends AbstractController
{
    /**
     * @Route("/answer/validate/{id}", name="answer_validate")
     */
    public function validate(Answer $answer)
    {
        // L'auteur de la question est-il le user qui valide ?
        // $user = $this->getUser();
        // if ($user !== $answer->getQuestion()->getUser()) {
        //     throw $this->createAccessDeniedException('Non autorisé.');
        // }
        // On remplace le code précédent par l'usage de notre Voter
        $this->denyAccessUnlessGranted('validateAnswer', $answer->getQuestion());

        // Valide réponse
        $answer->setIsValidated(true);
        // Valide question
        $answer->getQuestion()->setIsSolved(true);
        // Flush
        $this->getDoctrine()->getManager()->flush();
        // Flash
        $this->addFlash('success', 'Réponse acceptée');
        // Redirection
        return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
    }

    /**
     * @Route("/admin/answer/toggle/{id}", name="admin_answer_toggle")
     */
    public function adminToggle(Answer $answer = null)
    {
        if (null === $answer) {
            throw $this->createNotFoundException('Réponse non trouvée.');
        }

        // Inverse the boolean value via not (!)
        $answer->setIsBlocked(!$answer->getIsBlocked());
        // Save
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash('success', 'Réponse modérée.');

        return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
    }

}
