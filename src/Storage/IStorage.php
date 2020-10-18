<?php

namespace FreezyBee\Editrouble\Storage;

interface IStorage
{
    /**
     * @param mixed[] $params
     */
    public function getContent(string $name, array $params): string;

    /**
     * @param mixed[] $params
     */
    public function saveContent(string $name, array $params): void;
}
