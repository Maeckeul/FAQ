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
            ->setDescription('Désactive toutes les questions sans activité depuis plus de 7 jours')
            // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // $arg1 = $input->getArgument('arg1');

        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        // if ($input->getOption('option1')) {
        //     // ...
        // }

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
            if ($interval->days > 7) {
                // Si la différence est supérieure à 7, on désactive la question
                $question->setActive(false);
            }
        }
    
        // On flushe à la fin
        $this->em->flush();

        $io->success('Toutes les questions de plus de 7 jours ont été désactivées');

        return 0;
    }
}
