<?php

namespace App\Updater;

use App\Dto\GitRevision;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;

final readonly class GitHelper
{
    /**
     * @return iterable<GitRevision>
     */
    public function getRevisions(string $repositoryPath, string $startingCommit = 'HEAD'): iterable
    {
        $originalWorkingDirectory = getcwd() ?: throw new RuntimeException('Cannot get current working directory');
        $commandTemplate = 'git rev-list --max-count=50 --format=format:"%%H %%cd" --date=iso-strict %s';
        do {
            $command = sprintf($commandTemplate, $startingCommit);
            chdir($repositoryPath) || throw new InvalidArgumentException("The repository at '{$repositoryPath}' does not exist.");
            $output = trim(shell_exec($command));
            chdir($originalWorkingDirectory);
            $lines = explode("\n", $output);
            $i = 0;
            foreach ($lines as $line) {
                if ($i % 2 === 0) {
                    ++$i;
                    continue;
                }
                $parts = explode(" ", $line);
                yield new GitRevision(
                    revision: $parts[0],
                    dateTime: (new DateTimeImmutable($parts[1]))->setTimezone(new DateTimeZone('UTC')),
                );
                $startingCommit = $parts[0];
                ++$i;
            }
        } while (true);
    }

    public function getRevision(string $repositoryPath, string $revision): GitRevision
    {
        $originalWorkingDirectory = getcwd() ?: throw new RuntimeException('Cannot get current working directory');
        $command = "git show --pretty=format:\"%H %cd\" --date=iso-strict {$revision} -q";
        chdir($repositoryPath) || throw new InvalidArgumentException("The repository at '{$repositoryPath}' does not exist.");
        $output = trim(shell_exec($command));
        chdir($originalWorkingDirectory);
        $parts = explode(" ", $output);

        return new GitRevision(revision: $parts[0], dateTime: (new DateTimeImmutable($parts[1]))->setTimezone(new DateTimeZone('UTC')));
    }
}
