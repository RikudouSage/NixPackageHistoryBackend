<?php

namespace App\Updater;

use App\Dto\GitRevision;
use App\Entity\Package;
use DateTimeImmutable;
use Psr\Cache\CacheItemPoolInterface;

final readonly class PackageParser
{
    public function __construct(
        private CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * @return iterable<Package>
     */
    public function getPackages(GitRevision $revision): iterable
    {
        $cacheItem = $this->cache->getItem("revision-{$revision->revision}");
        if ($cacheItem->isHit()) {
            $json = $cacheItem->get();
        } else {
            $command = "nix-env -qaP --json -f https://github.com/NixOS/nixpkgs/archive/{$revision->revision}.zip";
            $output = shell_exec($command);
            $json = json_decode($output, true);
            $cacheItem->set($json);
            $this->cache->save($cacheItem);
        }

        if ($json === null) {
            return [];
        }

        foreach ($json as $packageName => $item) {
            yield (new Package($packageName, $item['version'], $revision->revision))
                ->setDatetime(DateTimeImmutable::createFromInterface($revision->dateTime));
        }
    }
}
