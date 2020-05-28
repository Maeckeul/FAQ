<?php

namespace App\Command;

use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class QuestionsDeactivateCommand extends Command
{
    protected static $defaultName = 'app:questions:deactivate';

    private $em;
    private $questionRepository;

    public function __construct(EntityManagerInterface $em, QuestionRepository $questionRepository)
    {
        parent::__construct();

        $this->em = $em;
        $this->questionRepository = $questionRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Désactive toutes les questions sans activité depuis plus de X jours (par défaut, 7 jours)')
            ->addArgument('questionId', InputArgument::OPTIONAL, 'Si vous souhaitez désactiver une question précise, indiquez son id')
            ->addOption('activate', 'a', InputOption::VALUE_NONE, 'Pour activer la question de questionId')
            ->addOption('days', 'd', InputOption::VALUE_REQUIRED, 'Nombre de jours limite d\'inactivité', 7)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $questionId = $input->getArgument('questionId');

        // $question vaut null si l'argument n'est pas précisé dans la commande
        // Sinon on reçoit bien la valeur
        // null étant un équivalent de false, sans argument, ce if ne s'exécute pas
        if ($questionId !== null) {
            $question = $this->questionRepository->find($questionId);

            if ($question === null) {
                $io->error('L\'id précisé ne correspond à aucune question en base de données');
                return 1;
            }
            // Si on a trouvé la question, on la (dés)active
            if ($input->getOption('activate')) {
                $question->setActive(true);
            } else {
                $question->setActive(false);
            }

            $this->em->flush();

            // On annonce que tout a fonctionné et on retourne 0 pour que le terminal comprenne que la commande a fonctionné
            $io->success('La question '.$questionId.' est bien (dés)activée');
            return 0;
        }

        // On récupère le nombre de jour limite à comparer pour désactiver les questions
        // Si l'option n'a pas précisé dans la commande, on reçoit, par défaut, 7
        $days = (int) $input->getOption('days');
        // Attention, si l'option entrée est un mot et non un nombre, $days == 0
        // On pourrait vérifier $days est supérieur à 0 et afficher un message d'erreur puir retourner l'entier 1

        // On recupère toutes les questions
        $questions = $this->questionRepository->findAll();

        // On vérifie l'activité de chaque question :
        foreach ($questions as $question) {
            // comparer la date de maintenant avec la date de la dernière activité de la question
            // On crée un DateTime pour maintenant
            $now = new \DateTime();

            // On doit calculer la différence entre updatedAt et maintenant
            // Peut-être que updatedAt vaut null, dans ce cas, on utilise createdAt
            if ($question->getUpdatedAt() === null) {
                // On demande à PHP de calculer l'intervalle de temps entre les deux DateTime
                $interval = $question->getCreatedAt()->diff($now);
            } else {
                $interval = $question->getUpdatedAt()->diff($now);
            }

            // $interval est un objet de la classe DateInterval
            // Sa propriété $days contient le nombre de jour entre deux dates (integer)
            if ($interval->days > $days) {
                // Si la différence est supérieure à 7, on désactive la question
                $question->setActive(false);
            }
        }
    
        // On flushe à la fin
        $this->em->flush();

        $io->success('Toutes les questions sans activité depuis plus de '.$days.' jours ont été désactivées');

        return 0;
    }
}
