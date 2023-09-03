<?php declare(strict_types=1);

namespace SwPlayground\Language\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Maintenance\System\Service\ShopConfigurator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sw-playground:language-set-default',
    description: 'Set the default language of the shop.',
    hidden: false
)]
class ChangeDefaultLanguageCommand extends Command
{

    /**
     * @internal
     */
    public function __construct(
        private readonly ShopConfigurator $shopConfigurator,
    ) {
        parent::__construct();

        $this->addOption(
            'locale',
            null,
            InputOption::VALUE_REQUIRED,
            'Locale name of the language to set as default.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        /** @var string $token */
        $locale = $input->getOption('locale');

        $this->shopConfigurator->setDefaultLanguage($locale);

        $io->success('Default language set to ' . $locale);

        return Command::SUCCESS;
    }
}
