<?php

namespace App\Command;

use App\Entity\PackageTag;
use App\Repository\PackageTagRepository;
use App\Repository\TagExtractionRuleRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use PDO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:extract-tags')]
final class ExtractTagsCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TagExtractionRuleRepository $ruleRepository,
        private readonly PackageTagRepository $tagRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeDatabase();
        foreach ($this->ruleRepository->findAll() as $rule) {
            $result = $this->connection->executeQuery('select name from packages where name regexp :regex group by name order by max(datetime) desc, name desc', ['regex' => $rule->getRegex()]);
            $packageNames = $result->fetchFirstColumn();
            $tag = $this->tagRepository->findOneBy(['tag' => $rule->getTagName()]) ?? (new PackageTag())->setTag($rule->getTagName());
            $tag->setPackageNames($packageNames);
            $this->entityManager->persist($tag);
        }
        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    private function initializeDatabase(): void
    {
        $pdo = $this->connection->getNativeConnection();
        assert($pdo instanceof PDO);
        $pdo->sqliteCreateFunction('regexp', fn(string $pattern, string $string): bool => (bool) preg_match('@' . str_replace('@', '\\@', $pattern) . '@', $string));
    }
}
