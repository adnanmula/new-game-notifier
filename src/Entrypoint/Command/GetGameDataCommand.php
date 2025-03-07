<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Entrypoint\Command;

use AdnanMula\Criteria\Criteria;
use AdnanMula\Criteria\Filter\Filter;
use AdnanMula\Criteria\Filter\FilterType;
use AdnanMula\Criteria\FilterField\FilterField;
use AdnanMula\Criteria\FilterGroup\AndFilterGroup;
use AdnanMula\Criteria\FilterValue\FilterOperator;
use AdnanMula\Criteria\FilterValue\StringArrayFilterValue;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GetGameDataCommand extends Command
{
    private const string NAME = 'steam:get:games';

    public function __construct(
        private readonly AppRepository $repository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Get game data')
            ->addArgument('appIds', InputArgument::IS_ARRAY, 'App ids');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $appIds = $input->getArgument('appIds');

        $validIds = [];
        $invalidIds = [];

        foreach ($appIds as $appId) {
            if (true === ctype_digit($appId)) {
                $validIds[] = (string) $appId;
            } else {
                $invalidIds[] = (string) $appId;
            }
        }

        $apps = $this->repository->search(new Criteria(
            null,
            null,
            null,
            new AndFilterGroup(
                FilterType::OR,
                new Filter(
                    new FilterField('app_id'),
                    new StringArrayFilterValue(...$validIds),
                    FilterOperator::IN,
                ),
            ),
        ));

        $indexedApps = [];
        foreach ($apps as $app) {
            $indexedApps[$app->appid] = $app;
        }

        if (\count($invalidIds) > 0) {
            $output->writeln('The following app ids are invalid:');
        }

        foreach ($invalidIds as $appId) {
            $output->writeln(' - ' . $appId);
        }

        foreach ($validIds as $appId) {
            $app = $indexedApps[$appId] ?? null;

            if (null === $app) {
                $output->writeln('> ' . $appId . ' is not present');

                continue;
            }

            $score = null === $app->reviewScore ? 'Unknown' : \sprintf('%s%% (%s reviews)', $app->reviewScore, $app->reviewAmount);
            $completionMain = null === $app->completionMain ? 'Unknown' : \round($app->completionMain / 60);
            $completionWithExtras = null === $app->completionMain ? 'Unknown' : \round($app->completionWithExtras / 60);
            $completionAvg = null === $app->completionMain ? 'Unknown' : \round($app->completionAvg / 60);
            $completionFull = null === $app->completionMain ? 'Unknown' : \round($app->completionFull / 60);

            $output->writeln('');
            $output->writeln(\sprintf('> %s (%s)', $app->name, $app->appid));
            $output->writeln(\sprintf(' - Playtime: %sm', $app->playedTime));
            $output->writeln(\sprintf(' - Score: %s', $score));
            $output->writeln(' - Completion time');
            $output->writeln(\sprintf('  - Main: %sh', $completionMain));
            $output->writeln(\sprintf('  - With extras: %sh', $completionWithExtras));
            $output->writeln(\sprintf('  - Avg: %sh', $completionAvg));
            $output->writeln(\sprintf('  - Full: %sh', $completionFull));
        }

        return self::SUCCESS;
    }
}
