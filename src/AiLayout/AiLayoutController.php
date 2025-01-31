<?php declare(strict_types=1);

namespace SwPlayground\AiLayout;

use OpenAI;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class AiLayoutController extends AbstractController
{
    private static string $wrapperPrompt = 'Use the following prompt "%inner_prompt%" and data "%data%". The dataset represents a layout for CMS pages. Edit and improve it according to the prompt. Please only return the final JSON data.';

    public function __construct() {
    }

    #[Route(path: '/api/_action/ai-layout/prompt', name: 'api.action.ai-layout/prompt', methods: ['POST'])]
    public function runAction(RequestDataBag $requestDataBag, Context $context): Response
    {
        $data = $requestDataBag->all();
        $client = OpenAI::client(getenv('OPENAI_API_KEY'));

        // provide some documentation
        $prompt = $this->buildPrompt($data['prompt'], $data['pageData']);

        $result = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        // parse result
        $newPage = $result->choices[0]->message->content;

        dd($newPage);

        return new JsonResponse($newPage);
    }

    private function buildPrompt(string $input, array $data): string
    {
        return str_replace('%data%', json_encode($data), str_replace('%inner_prompt%', $input, AiLayoutController::$wrapperPrompt));
    }
}
