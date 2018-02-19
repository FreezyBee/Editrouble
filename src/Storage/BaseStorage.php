<?php

namespace FreezyBee\Editrouble\Storage;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\SmartObject;

/**
 * Class BaseStorage
 * @package FreezyBee\Editrouble\Storage
 */
abstract class BaseStorage
{
    use SmartObject;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * BaseStorage constructor.
     * @param IStorage $cacheStorage
     */
    public function __construct(IStorage $cacheStorage)
    {
        $this->cache = new Cache($cacheStorage, 'editrouble');
    }

    /**
     * @param $namespace
     * @return array
     */
    public function loadCachedNamespace($namespace)
    {
        if (isset($this->loadedData[$namespace])) {
            return $this->loadedData[$namespace];
        } else {
            return $this->loadedData[$namespace] = $this->cache->load($namespace);
        }
    }

    /**
     * @param $namespace
     * @param $data
     * @return array
     */
    public function saveCachedNamespace($namespace, $data)
    {
        return $this->cache->save($namespace, $data);
    }

    /**
     * @param $name
     * @return \stdClass
     */
    protected function decodeNames($name)
    {
        $names = explode('_', $name);
        $count = count($names);

        $result = new \stdClass;

        if ($count == 1) {
            $result->namespace = '';
            $result->name = $names[0];
        } else {
            $result->namespace = $names[0];
            $result->name = $names[1];
        }

        return $result;
    }
}
