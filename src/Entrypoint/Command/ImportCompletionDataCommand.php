<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Entrypoint\Command;

use AdnanMula\Criteria\Criteria;
use AdnanMula\Criteria\Filter\Filter;
use AdnanMula\Criteria\Filter\FilterType;
use AdnanMula\Criteria\FilterField\FilterField;
use AdnanMula\Criteria\FilterGroup\AndFilterGroup;
use AdnanMula\Criteria\FilterValue\FilterOperator;
use AdnanMula\Criteria\FilterValue\IntFilterValue;
use AdnanMula\Criteria\FilterValue\NullFilterValue;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Completion\HltbClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportCompletionDataCommand extends Command
{
    private const string NAME = 'new-game-notifier:completion';

    public function __construct(
        private readonly HltbClient $client,
        private readonly AppRepository $repository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Import completion data')
            ->addOption('appIds', 'a', InputOption::VALUE_OPTIONAL, 'Comma separated app ids')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Amount to import', 10);
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
            null,
            $limit,
            null,
            new AndFilterGroup(
                FilterType::AND,
                new Filter(new FilterField('completion_main'), new NullFilterValue(), FilterOperator::IS_NULL),
            ),
            new AndFilterGroup(FilterType::OR, ...$filters),
        ));

        foreach ($apps as $app) {
            $completionData = $this->client->completionData($app->name);

            $this->repository->updateCompletionData($app->appid, $completionData);

            $output->writeln(\sprintf(
                'Imported App: %s, Completion main: %s, with extras: %s, full: %s, avg: %s',
                $app->appid,
                $completionData->completionMain,
                $completionData->completionWithExtras,
                $completionData->completionFull,
                $completionData->completionAvg,
            ));
        }

        return self::SUCCESS;
    }
}
