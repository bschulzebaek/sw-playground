<?php declare(strict_types=1);

namespace SwPlayground\License\Command;

use GuzzleHttp\Client;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sw-playground:license-create',
    description: 'Create a Commercial license for local development.',
    hidden: false
)]
class CreateCommand extends Command
{
    private const API_URL = 'https://api.shopware.com/internal/commerciallicensekeysforshopwaredevelopers';

    private const KEY_HOST = 'core.store.licenseHost';
    private const KEY_LICENSE = 'core.store.licenseKey';

    private const DEFAULT_HOST = 'localhost';
    private const DEFAULT_FLAGS = [
        'RULE_BUILDER-6864922',
        'RULE_BUILDER-1967308',
        'RULE_BUILDER-4978012',

        'FLOW_BUILDER-4644229',
        'FLOW_BUILDER-1270531',
        'FLOW_BUILDER-4142679',

        'FLOW_PREVIEW-3977814',
        'FLOW_PREVIEW-5356127',
        'FLOW_PREVIEW-4251946',

        'CUSTOM_PRICES-2356553',
        'CUSTOM_PRICES-4458487',
        'CUSTOM_PRICES-1673073',

        'SELF_SERVICE-4798784',
        'SELF_SERVICE-2706913',
        'SELF_SERVICE-1224443',

        'SUBSCRIPTIONS-2437281',
        'SUBSCRIPTIONS-3156213',
        'SUBSCRIPTIONS-1020493',
        'SUBSCRIPTIONS-6549379',

        'CUSTOM_ENTITIES-4396574',
        'CUSTOM_ENTITIES-3064313',
        'CUSTOM_ENTITIES-4868357',
        'CUSTOM_ENTITIES-1737569',
        'CUSTOM_ENTITIES-2062848',
        'CUSTOM_ENTITIES-3528213',

        'PUBLISHER-3469475',
        'PUBLISHER-2617250',
        'PUBLISHER-3972588',

        'ADVANCED_SEARCH-1376205',
        'ADVANCED_SEARCH-3068620',
        'ADVANCED_SEARCH-2404952',
        'ADVANCED_SEARCH-6030617',

        'MULTI_INVENTORY-3178550',
        'MULTI_INVENTORY-3749997',
        'MULTI_INVENTORY-3711815',

        'RETURNS_MANAGEMENT-8450687',
        'RETURNS_MANAGEMENT-1630550',
        'RETURNS_MANAGEMENT-4960984',

        'FLOW_BUILDER-1811993',
        'FLOW_BUILDER-8996729',
        'FLOW_BUILDER-8478732',

        'TEXT_GENERATOR-2946372',
        'TEXT_GENERATOR-2736493',
        'TEXT_GENERATOR-2209427',

        'CHECKOUT_SWEETENER-8945908',
        'CHECKOUT_SWEETENER-3877631',
        'CHECKOUT_SWEETENER-0270128',

        'FLOW_BUILDER-8472079',
        'FLOW_BUILDER-7563087',
        'FLOW_BUILDER-6654893',

        'IMAGE_CLASSIFICATION-0171982',
        'IMAGE_CLASSIFICATION-2910311',

        'PROPERTY_EXTRACTOR-3361467',
        'PROPERTY_EXTRACTOR-4927340',
        'PROPERTY_EXTRACTOR-4802372',

        'REVIEW_SUMMARY-8147095',

        'REVIEW_TRANSLATOR-1649854',

        'CONTENT_GENERATOR-1759573',
        'CONTENT_GENERATOR-9573958',
        'CONTENT_GENERATOR-5780304',

        'EXPORT_ASSISTANT-4992823',
        'EXPORT_ASSISTANT-0490710',
        'EXPORT_ASSISTANT-2007020',

        'QUICK_ORDER-9771104',
        'QUICK_ORDER-7355963',
        'QUICK_ORDER-8974312',

        'EMPLOYEE_MANAGEMENT-4838834',
        'EMPLOYEE_MANAGEMENT-1264745',
        'EMPLOYEE_MANAGEMENT-3702619',

        'QUOTE_MANAGEMENT-8702512',
        'QUOTE_MANAGEMENT-6302947',
        'QUOTE_MANAGEMENT-5019169',

        'CAPTCHA-8765432',
        'CAPTCHA-5698712',
        'CAPTCHA-1295483',
        'CAPTCHA-3581792',

        'NATURAL_LANGUAGE_SEARCH-5828669',
        'NATURAL_LANGUAGE_SEARCH-6923702',
        'NATURAL_LANGUAGE_SEARCH-4165825',
        'NATURAL_LANGUAGE_SEARCH-9467395',

        'IMAGE_UPLOAD_SEARCH-5086829',
        'IMAGE_UPLOAD_SEARCH-4973248',
        'IMAGE_UPLOAD_SEARCH-2194725',
        'IMAGE_UPLOAD_SEARCH-9264978',

        'ORDER_APPROVAL-3419482',
        'ORDER_APPROVAL-7482547',
        'ORDER_APPROVAL-5829573',

        'SSO-9302912',
        'SSO-5061697',
        'SSO-4504299',

        'SPATIAL_CMS_ELEMENT-6234242',
        'SPATIAL_CMS_ELEMENT-2959075',
        'SPATIAL_CMS_ELEMENT-5133146'
    ];

    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly Client $client,
    ) {
        parent::__construct();

        $this->addOption(
            'token',
            null,
            InputOption::VALUE_REQUIRED,
            'X-Shopware-Token from SBP Account.',
        );

        $this->addOption(
            'host',
            null,
            InputOption::VALUE_OPTIONAL,
            'Hostname of local dev environment.',
            self::DEFAULT_HOST,
        );

        $this->addOption(
            'flags',
            null,
            InputOption::VALUE_OPTIONAL,
            'Comma-separated list of activated license flags.',
            self::DEFAULT_FLAGS,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        /** @var string $token */
        $token = $input->getOption('token');
        /** @var string $host */
        $host = $input->getOption('host');
        /** @var array $flags */
        $flags = $input->getOption('flags');

        if (!$token) {
            $io->error('No token provided!');
            return Command::FAILURE;
        }

        $license = $this->getLicense(
            $this->createHeaders($token),
            $this->createBody($host, $flags),
        );

        $this->updateLicense($host, $license);

        return Command::SUCCESS;
    }

    private function updateLicense(string $host, string $license): void
    {
        $this->systemConfigService->set(self::KEY_HOST, $host);
        $this->systemConfigService->set(self::KEY_LICENSE, $license);
    }

    private function getLicense(array $headers, array $body): string
    {
        $response = $this->client->post(self::API_URL, [
            'headers' => $headers,
            'json' => $body,
        ]);

        return json_decode($response->getBody()->getContents())->key;
    }

    private function createHeaders(string $token): array
    {
        return [
            'Content-Type' => 'application/json',
            'X-Shopware-Token' => $token,
        ];
    }

    private function createBody(string $host, array $flags): array
    {
        $flagsAssociative = array_combine($flags, array_fill(0, count($flags), true));

        return [
            'licenseHost' => $host,
            'licenseToggles' => $flagsAssociative,
        ];
    }
}
