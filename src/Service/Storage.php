<?php

namespace App\Service;

interface Storage
{
    public function createObject(string $key, string $content, string $contentType): void;
    public function getObjectLink(string $key): string;
    public function exists(string $key): bool;
}
