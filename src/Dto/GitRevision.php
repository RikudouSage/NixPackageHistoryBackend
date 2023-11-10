<?php

namespace App\Dto;

use DateTimeInterface;

final readonly class GitRevision
{
    public function __construct(
        public string $revision,
        public DateTimeInterface $dateTime,
    ) {
    }
}
