<?php

namespace FreezyBee\Editrouble\Storage;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\SmartObject;
use stdClass;

abstract class BaseStorage
{
    use SmartObject;

    protected Cache $cache;

    /** @var mixed[] */
    protected array $loadedData;

    public function __construct(IStorage $cacheStorage)
    {
        $this->cache = new Cache($cacheStorage, 'editrouble');
    }

    /**
     * @return mixed[]
     */
    public function loadCachedNamespace(string $namespace): array
    {
        return $this->loadedData[$namespace] ?? ($this->loadedData[$namespace] = $this->cache->load($namespace) ?? []);
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    public function saveCachedNamespace(string $namespace, array $data): array
    {
        return $this->cache->save($namespace, $data);
    }

    protected function decodeNames(string $name): stdClass
    {
        $names = explode('_', $name);
        $count = count($names);

        $result = new stdClass;

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
