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
        $command = "git rev-list --format=format:\"%H %cd\" --date=iso-strict {$startingCommit}";
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
            ++$i;
        }
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
