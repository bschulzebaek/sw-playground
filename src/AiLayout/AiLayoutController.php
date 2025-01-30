<?php declare(strict_types=1);

namespace SwPlayground\AiLayout;

use Shopware\Administration\Controller\Exception\AppByNameNotFoundException;
use Shopware\Administration\Controller\Exception\MissingAppSecretException;
use Shopware\Core\Framework\App\ActionButton\AppAction;
use Shopware\Core\Framework\App\ActionButton\Executor;
use Shopware\Core\Framework\App\AppEntity;
use Shopware\Core\Framework\App\Hmac\QuerySigner;
use Shopware\Core\Framework\App\Manifest\Exception\UnallowedHostException;
use Shopware\Core\Framework\App\Payload\AppPayloadServiceHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class AiLayoutController extends AbstractController
{
    private static string $wrapperPrompt = '"%inner_prompt%" "%data%"';

    public function __construct() {
    }

    #[Route(path: '/api/_action/ai-layout/prompt', name: 'api.action.ai-layout/prompt', methods: ['POST'])]
    public function runAction(RequestDataBag $requestDataBag, Context $context): Response
    {
        $data = $requestDataBag->all();
        $client = \OpenAI::client(getenv('OPENAI_API_KEY'));

        $prompt = $this->buildPrompt($data['prompt'], $data['pageData']);

        $result = $client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        dd($result->choices[0]->message->content);

        return new JsonResponse([
            'prompt' => $data['prompt'],
            'pageData' => $data['pageData'],
        ]);
    }

    private function buildPrompt(string $input, array $data): string
    {
        return str_replace('%data%', json_encode($data), str_replace('%inner_prompt%', $input, self::wrapperPrompt));
    }
}
