<?php

namespace App\Service;

use AsyncAws\SimpleS3\SimpleS3Client;
use DateTimeImmutable;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class S3Storage implements Storage
{
    public function __construct(
        private SimpleS3Client $s3client,
        #[Autowire('%app.storage.s3_bucket%')]
        private string $bucket,
    ) {
    }

    #[Override]
    public function createObject(string $key, string $content, string $contentType): void
    {
        $this->s3client->upload($this->bucket, $key, $content, [
            'ContentType' => $contentType,
        ]);
    }

    #[Override]
    public function getObjectLink(string $key): string
    {
        return $this->s3client->getPresignedUrl($this->bucket, $key, new DateTimeImmutable('+10 minutes'));
    }

    #[Override]
    public function exists(string $key): bool
    {
        return $this->s3client->has($this->bucket, $key);
    }
}
