<?php

namespace App\Controller;

use App\Entity\Question;
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
    public function homepage()
    {
        return $this->render('question/homepage.html.twig');
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
    public function show($slug, MarkdownHelper $markdownHelper)
    {
        if ($this->isDebug) {
            $this->logger->info('We are in debug mode!');
        }

        $answers = [
            'Make sure your cat is sitting `purrrfectly` still 🤣',
            'Honestly, I like furry shoes better than MY cat',
            'Maybe... try saying the spell backwards?',
        ];
        $questionText = 'I\'ve been turned into a cat, any *thoughts* on how to turn back? While I\'m **adorable**, I don\'t really care for cat food.';

        $parsedQuestionText = $markdownHelper->parse($questionText);

        return $this->render('question/show.html.twig', [
            'question' => ucwords(str_replace('-', ' ', $slug)),
            'questionText' => $parsedQuestionText,
            'answers' => $answers,
        ]);
    }
}
