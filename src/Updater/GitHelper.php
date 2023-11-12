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
        $isFirst = true;
        $commandTemplate = 'git log --pretty=format:"%%H %%cd" --date=iso-strict %1$s~50..%1$s';
        do {
            $command = sprintf($commandTemplate, $startingCommit);
            if ($isFirst) {
                $isFirst = false;
            } else {
                $command .= '^';
            }
            chdir($repositoryPath) || throw new InvalidArgumentException("The repository at '{$repositoryPath}' does not exist.");
            $output = trim(shell_exec($command));
            chdir($originalWorkingDirectory);
            $lines = explode("\n", $output);
            foreach ($lines as $line) {
                $parts = explode(" ", $line);
                yield new GitRevision(
                    revision: $parts[0],
                    dateTime: (new DateTimeImmutable($parts[1]))->setTimezone(new DateTimeZone('UTC')),
                );
                $startingCommit = $parts[0];
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
