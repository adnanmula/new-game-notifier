<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Entrypoint\Command;

use AdnanMula\Criteria\Criteria;
use AdnanMula\Criteria\Filter\CompositeFilter;
use AdnanMula\Criteria\Filter\Filter;
use AdnanMula\Criteria\Filter\FilterOperator;
use AdnanMula\Criteria\Filter\Filters;
use AdnanMula\Criteria\Filter\FilterType;
use AdnanMula\Criteria\FilterField\FilterField;
use AdnanMula\Criteria\FilterValue\IntFilterValue;
use AdnanMula\Criteria\FilterValue\NullFilterValue;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppCompatibility;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Steam\ProtondbClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'steam:import:compatibility', description: 'Import compatibility data from protondb')]
final class ImportProtondbDataCommand extends Command
{
    public function __construct(
        private readonly ProtondbClient $client,
        private readonly AppRepository $repository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('appIds', 'a', InputOption::VALUE_OPTIONAL, 'Comma separated app ids')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Amount to import', 25);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');

        $appIds = null === $input->getOption('appIds')
            ? null
            : \array_map('intval', \explode(',', $input->getOption('appIds')));

        $filters = [];

        if (null !== $appIds) {
            foreach ($appIds as $appId) {
                $filters[] = new Filter(new FilterField('app_id'), new IntFilterValue($appId), FilterOperator::EQUAL);
            }
        }

        $apps = $this->repository->search(new Criteria(
            filters: new Filters(
                FilterType::AND,
                new Filter(new FilterField('compatibility'), new NullFilterValue(), FilterOperator::IS_NULL),
                new CompositeFilter(FilterType::OR, ...$filters,),
            ),
            limit: $limit,
        ));

        foreach ($apps as $app) {
            try {
                $compatibilityData = $this->client->compatibility($app->appid);
                $this->repository->updateCompatibilityData($app->appid, $compatibilityData);

                $output->writeln(\sprintf(
                    'Imported App: %s with Compatibility: %s',
                    $app->appid,
                    $compatibilityData->value,
                ));
            } catch (\Throwable) {
                $this->repository->updateCompatibilityData($app->appid, AppCompatibility::UNKNOWN);
                $output->writeln(\sprintf('Error App: %s', $app->appid));
            }
        }

        return self::SUCCESS;
    }
}
