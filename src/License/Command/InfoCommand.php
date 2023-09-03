<?php declare(strict_types=1);

namespace SwPlayground\License\Command;

use Lcobucci\JWT\Configuration;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sw-playground:license-info',
    description: 'Show information about currently set license key.',
    hidden: false
)]
class InfoCommand extends Command
{
    private const KEY_LICENSE = 'core.store.licenseKey';

    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly Configuration $configuration
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        $jwt = $this->getLicense();

        if (!$jwt) {
            $io->error('No license key set!');
        } else {
            $this->printData($io, $jwt);
        }

        return Command::SUCCESS;
    }

    private function getLicense(): string
    {
        return $this->systemConfigService->getString(self::KEY_LICENSE);
    }

    private function printData(ShopwareStyle $io, string $jwt): void
    {
        $data = $this->configuration->parser()->parse($jwt);
        $claims = $data->claims();

        $host = $claims->get('aud')[0];
        $flags = $claims->get('license-toggles');
        $owner = $claims->get('swemp');

        if ($data->isExpired(date_create())) {
            $io->warning('License is expired!');
        }

        $io->title('License information');
        $io->writeln([
            'Owner: ' . $owner,
            'Host: ' . $host,
            'Flags: ',
        ]);

        foreach ($flags as $flag => $active) {
            $io->writeln(' - ' . $flag);
        }

        $io->writeln('');
    }
}
