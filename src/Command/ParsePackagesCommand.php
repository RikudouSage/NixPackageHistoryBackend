<?php

namespace App\Command;

use App\Repository\PackageRepository;
use App\Updater\GitHelper;
use App\Updater\PackageParser;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:parse-packages')]
final class ParsePackagesCommand extends Command
{
    public function __construct(
        private readonly PackageParser $packageParser,
        private readonly GitHelper $gitHelper,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $pathToNixpkgs,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption(
                'start-at',
                null,
                InputOption::VALUE_REQUIRED,
                'The ref to start with',
                'HEAD',
            )
            ->addOption(
                'skip',
                null,
                InputOption::VALUE_REQUIRED,
                'Amount of days to skip between imports',
                0,
            )
            ->addOption(
                'stop-at',
                null,
                InputOption::VALUE_REQUIRED,
                "Datetime to stop at - anything older than this value will not be parsed",
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $ref = $input->getOption('start-at');
        $skipDays = $input->getOption('skip');
        $stopAt = $input->getOption('stop-at') ? new DateTimeImmutable($input->getOption('stop-at')) : null;

        $skipDays = $skipDays ? new DateInterval("P{$skipDays}D") : null;
        /** @var DateTimeImmutable|null $lastDate */
        $lastDate = null;

        $connection = $this->entityManager->getConnection();
        foreach ($this->gitHelper->getRevisions($this->pathToNixpkgs, $ref) as $revision) {
            if ($stopAt && $revision->dateTime < $stopAt) {
                $io->note("Stopping at {$revision->revision} because the revision's date ({$revision->dateTime->format('c')}) is older than specified ({$stopAt->format('c')})");
                break;
            }
            if ($lastDate !== null && $skipDays !== null && $lastDate->sub($skipDays) < $revision->dateTime) {
                $io->note("Skipping revision {$revision->revision} ({$revision->dateTime->format('c')})");
                continue;
            }

            $io->note("Parsing revision '{$revision->revision}' ({$revision->dateTime->format('c')})");
            $insertQuery = "INSERT INTO packages (name, version, revision, datetime) VALUES ";
            $i = 1;
            foreach ($this->packageParser->getPackages($revision) as $package) {
                if ($io->isVerbose()) {
                    $io->note("Adding or updating package '{$package->getName()}' ({$package->getVersion()}) at {$revision->revision}");
                }
                $insertQuery .= "('{$package->getName()}', '{$package->getVersion()}', '{$package->getRevision()}', '{$package->getDatetime()->format('c')}'), ";
                ++$i;
            }

            if (str_ends_with($insertQuery, 'VALUES ')) {
                $io->warning('No packages found, skipping');
                continue;
            }
            $insertQuery = substr($insertQuery, 0, -2);
            $insertQuery .= " ON CONFLICT (name, version) DO UPDATE SET revision = excluded.revision, datetime = excluded.datetime WHERE excluded.datetime > packages.datetime";
            $connection->executeStatement($insertQuery);
            $lastDate = $revision->dateTime;
            $io->success("{$revision->revision} parsed with {$i} packages added/updated");
        }
        return self::SUCCESS;
    }
}
