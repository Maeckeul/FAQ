<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\AnswerType;
use App\Form\QuestionType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    /**
     * @Route("/", name="question_list")
     * @Route("/tag/{name}", name="question_list_by_tag")
     * @ParamConverter("tag", class="App:Tag")
     */
    public function list(Request $request, QuestionRepository $questionRepository, Tag $tag = null)
    {
        // On vérifie si on vient de la route "question_list_by_tag"
        if($request->attributes->get('_route') == 'question_list_by_tag' && $tag === null) {
            // On récupère le name passé dans l'attribut de requête
            $params = $request->attributes->get('_route_params');
            $selectedTag = $params['name'];
            // Equivaut à $selectedTag = $request->attributes->get('_route_params')['name'];

            // Flash + redirect
            $this->addFlash('success', 'Le mot-clé "'.$selectedTag.'" n\'existe pas. Affichage de toutes les questions.');
            return $this->redirectToRoute('question_list');
        }

        // On va chercher la liste des questions par ordre inverse de date
        if($tag) {
            // Avec tag
            $questions = $questionRepository->findByTag($tag);
            $selectedTag = $tag->getName();
        } else {
            // Sans tag
            $questions = $questionRepository->findBy(['isBlocked' => false], ['createdAt' => 'DESC']);
            $selectedTag = null;
        }

        // Nuage de mots-clés
        $tags = $this->getDoctrine()->getRepository(Tag::class)->findBy([], ['name' => 'ASC']);

        return $this->render('question/index.html.twig', [
            'questions' => $questions,
            'tags' => $tags,
            'selectedTag' => $selectedTag,
        ]);
    }

    /**
     * @Route("/question/{id}", name="question_show", requirements={"id": "\d+"})
     */
    public function show(Question $question, Request $request, UserRepository $userRepository, AnswerRepository $answerRepository)
    {
        // Is question blocked ?
        if ($question->getIsBlocked()) {
            throw $this->createAccessDeniedException('Non autorisé.');
        }

        $answer = new Answer();

        $form = $this->createForm(AnswerType::class, $answer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $answer = $form->getData();
            // On associe Réponse
            $answer->setQuestion($question);

            // On associe le user connecté à la réponse
            $answer->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($answer);
            $entityManager->flush();

            $this->addFlash('success', 'Réponse ajoutée');

            return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
        }

        // Réponses non bloquées
        $answersNonBlocked = $answerRepository->findBy([
            'question' => $question,
            'isBlocked' => false,
        ]);

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answersNonBlocked' => $answersNonBlocked,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/question/add", name="question_add")
     */
    public function add(Request $request, UserRepository $userRepository)
    {
        $question = new Question();

        $form = $this->createForm(QuestionType::class, $question);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $question = $form->getData();

            // On associe le user connecté à la question
            $question->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($question);
            $entityManager->flush();

            $this->addFlash('success', 'Question ajoutée');

            return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
        }

        return $this->render('question/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/question/toggle/{id}", name="admin_question_toggle")
     */
    public function adminToggle(Question $question = null)
    {
        if (null === $question) {
            throw $this->createNotFoundException('Question non trouvée.');
        }

        // Inverse the boolean value via not (!)
        $question->setIsBlocked(!$question->getIsBlocked());
        // Save
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash('success', 'Question modérée.');

        return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
    }

}
