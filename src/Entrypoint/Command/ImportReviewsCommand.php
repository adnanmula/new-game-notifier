<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Entrypoint\Command;

use AdnanMula\Criteria\Criteria;
use AdnanMula\Criteria\Filter\Filter;
use AdnanMula\Criteria\Filter\FilterType;
use AdnanMula\Criteria\FilterField\FilterField;
use AdnanMula\Criteria\FilterGroup\AndFilterGroup;
use AdnanMula\Criteria\FilterValue\FilterOperator;
use AdnanMula\Criteria\FilterValue\NullFilterValue;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Steam\SteamClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportReviewsCommand extends Command
{
    private const string NAME = 'steam:import:reviews';

    public function __construct(
        private SteamClient $client,
        private AppRepository $repository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Import review scores')
            ->addOption('appIds', 'a', InputOption::VALUE_OPTIONAL, 'Comma separated app ids')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Amount to import', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $limit = (int) $input->getOption('limit');
        $appIds = null === $input->getOption('appIds')
            ? null
            : \array_map('intval', \explode(',', $input->getOption('appIds')));

        if (null === $appIds) {
            $apps = $this->repository->search(
                new Criteria(
                    null,
                    $limit,
                    null,
                    new AndFilterGroup(
                        FilterType::AND,
                        new Filter(new FilterField('review_score'), new NullFilterValue(), FilterOperator::IS_NULL),
                    ),
                ),
            );

            $appIds = array_map(static fn (App $a): int => $a->appid, $apps);
        }

        foreach ($appIds as $appId) {
            [$score, $amount] = $this->client->appReviews((int) $appId);

            $this->repository->updateReviewScore((int) $appId, $score, $amount);

            $output->writeln(sprintf('Imported App: %s, Score: %s Amount: %s', $appId, $score, $amount));
        }

        return self::SUCCESS;
    }
}
