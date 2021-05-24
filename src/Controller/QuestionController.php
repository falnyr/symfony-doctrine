<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Service\MarkdownHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    private $logger;
    private $isDebug;

    public function __construct(LoggerInterface $logger, bool $isDebug)
    {
        $this->logger = $logger;
        $this->isDebug = $isDebug;
    }


    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(QuestionRepository $repository)
    {
        $questions = $repository->findAllAskedOrderByNewest();

        return $this->render('question/homepage.html.twig', [
            'questions' => $questions,
        ]);
    }

    /**
     * @Route("/questions/new")
     */
    public function new(EntityManagerInterface $entityManager)
    {
        $question = new Question();
        $question->setName('Foobar');
        $question->setSlug('foobar-'.rand(1,1000));
        $question->setQuestion(<<<EOF
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla et orci eu ligula posuere sodales ut vel odio. Quisque viverra, eros et elementum lobortis, dolor nisi varius nisi, id blandit ipsum felis at metus. Quisque nec est vel lacus elementum ornare et quis tortor. Morbi sollicitudin turpis at dictum luctus. In magna erat, tempus vitae porttitor eget, porta id tellus. Nulla facilisi. Maecenas porttitor iaculis felis nec rutrum. Proin gravida lacus non vulputate euismod. Phasellus sit amet ultrices nunc. Duis sollicitudin auctor maximus. Cras nec dapibus nisi. Proin tortor turpis, aliquet ac arcu posuere, pellentesque fermentum nisi.
EOF
);

        if (rand(0,1)) {
            $question->setAskedAt(new \DateTime(sprintf("-%d days", rand(1, 100))));
        }

        $entityManager->persist($question);
        $entityManager->flush();

        return new Response($question->getId());
    }

    /**
     * @Route("/questions/{slug}", name="app_question_show")
     */
    public function show($slug, MarkdownHelper $markdownHelper, EntityManagerInterface $entityManager)
    {
        if ($this->isDebug) {
            $this->logger->info('We are in debug mode!');
        }

        $repository = $entityManager->getRepository(Question::class);
        /** @var Question|null $question */
        $question = $repository->findOneBy(['slug' => $slug]);
        if (!$question) {
            throw $this->createNotFoundException(sprintf('No question found for %s', $slug));
        }

        $answers = [
            'Make sure your cat is sitting `purrrfectly` still 🤣',
            'Honestly, I like furry shoes better than MY cat',
            'Maybe... try saying the spell backwards?',
        ];

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answers' => $answers,
        ]);
    }
}
