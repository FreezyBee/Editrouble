<?php

namespace FreezyBee\Editrouble\Storage;

/**
 * Interface IStorage
 * @package FreezyBee\Editrouble\Storage
 */
interface IStorage
{
    /**
     * @param $name
     * @param $params
     * @return mixed
     */
    public function getContent($name, $params);

    /**
     * @param $name
     * @param $params
     */
    public function saveContent($name, $params);
}
