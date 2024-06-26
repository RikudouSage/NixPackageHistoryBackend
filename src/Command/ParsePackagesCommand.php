<?php

namespace App\Command;

use App\Entity\Package;
use App\Enum\NamedSetting;
use App\Repository\PackageRepository;
use App\Service\Settings;
use App\Updater\GitHelper;
use App\Updater\PackageParser;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\JsonResponse;

#[AsCommand('app:parse-packages')]
final class ParsePackagesCommand extends Command
{
    public function __construct(
        private readonly PackageParser $packageParser,
        private readonly GitHelper $gitHelper,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $pathToNixpkgs,
        private readonly Settings $settings,
        private readonly PackageRepository $packageRepository,
    ) {
        parent::__construct();
    }

    #[Override]
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

    #[Override]
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
            $insertQuery = "INSERT INTO packages (name, version, revision, datetime, unfree, platforms) VALUES ";
            $i = 1;
            foreach ($this->packageParser->getPackages($revision) as $package) {
                if ($io->isVerbose()) {
                    $io->note("Adding or updating package '{$package->getName()}' ({$package->getVersion()}) at {$revision->revision}");
                }
                $unfree = $package->isUnfree() === null ? 'NULL' : ($package->isUnfree() ? 'true' : 'false');
                $platformsSqlValue = json_encode($package->getPlatforms() ?? [], flags: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $insertQuery .= "('{$package->getName()}', '{$package->getVersion()}', '{$package->getRevision()}', '{$package->getDatetime()->format('c')}', {$unfree}, '{$platformsSqlValue}'), ";
                ++$i;
            }

            if (str_ends_with($insertQuery, 'VALUES ')) {
                $io->warning('No packages found, skipping');
                continue;
            }
            $insertQuery = substr($insertQuery, 0, -2);
            $insertQuery .= " ON CONFLICT (name, version) DO UPDATE SET revision = excluded.revision, datetime = excluded.datetime, unfree = excluded.unfree, platforms = excluded.platforms WHERE excluded.datetime > packages.datetime";
            $connection->executeStatement($insertQuery);
            $lastDate = $revision->dateTime;
            $io->success("{$revision->revision} parsed with {$i} packages added/updated");
        }

        $latest = $this->packageRepository->createQueryBuilder('p')
            ->setMaxResults(1)
            ->orderBy('p.datetime', 'DESC')
            ->getQuery()
            ->getSingleResult();
        if (!$latest instanceof Package) {
            $io->error('Failed getting latest revision');
            return self::FAILURE;
        }

        $this->settings->setSetting(NamedSetting::LatestRevision, $latest->getRevision());
        $this->settings->setSetting(NamedSetting::LatestRevisionDatetime, $latest->getDatetime()?->format('c'));

        return self::SUCCESS;
    }
}
