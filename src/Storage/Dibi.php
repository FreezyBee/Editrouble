<?php

namespace FreezyBee\Editrouble\Storage;

use Dibi\Connection;
use Dibi\DriverException;
use Nette\Caching;

/**
 * Class Dibi
 * @package FreezyBee\Editrouble\Storage
 */
class Dibi extends BaseStorage implements IStorage
{
    const TABLE = 'editrouble_content';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Doctrine constructor.
     * @param Caching\IStorage $storage
     * @param Connection $connection
     */
    public function __construct(Caching\IStorage $storage, Connection $connection)
    {
        parent::__construct($storage);
        $this->connection = $connection;
    }

    /**
     * @param $name
     * @param $params
     * @return string|false
     */
    public function getContent($name, $params)
    {
        $names = $this->decodeNames($name);
        $locale = (isset($params['locale'])) ? $params['locale'] : '';

        $records = $this->loadCachedNamespace($names->namespace);

        if (isset($records[$names->namespace][$names->name][$locale])) {
            return $records[$names->namespace][$names->name][$locale];
        } else {
            return $this->findContentAndFillCache($names->namespace, $names->name, $locale);
        }
    }

    /**
     * @param $namespace
     * @param $name
     * @param $locale
     * @return string
     * @throws DriverException
     */
    private function findContentAndFillCache($namespace, $name, $locale)
    {
        try {
            /** @var array $result */
            $result = $this->connection
                ->select('*')
                ->from(self::TABLE)
                ->where(['namespace%s' => $namespace])
                ->fetchAll();
        } catch (DriverException $e) {
            // check table not exist
            if ($e->getCode() == 1146) {
                $this->connection
                    ->query('
                      CREATE TABLE [' . self::TABLE . '] (
                      [id] int(11) NOT NULL AUTO_INCREMENT,
                      [namespace] varchar(255) NOT NULL,
                      [name] varchar(255) NOT NULL,
                      [locale] varchar(255) NOT NULL,
                      [content] text NOT NULL,
                      PRIMARY KEY ([id]),
                      UNIQUE KEY [uniq_record] ([namespace],[name],[locale])
                    );');

                $result = [];
            } else {
                throw $e;
            }
        }

        $tmp = [];

        foreach ($result as $item) {
            if (!isset($tmp[$item['name']])) {
                $tmp[$item['name']] = [];
            }

            $tmp[$item['namespace']][$item['name']][$item['locale']] = $item['content'];
        }

        $this->saveCachedNamespace($namespace, $tmp);

        return isset($tmp[$namespace][$name][$locale]) ? $tmp[$namespace][$name][$locale] : '';
    }

    /**
     * @param $name
     * @param $params
     */
    public function saveContent($name, $params)
    {
        $names = $this->decodeNames($name);
        $locale = (isset($params['locale'])) ? $params['locale'] : '';

        $rowId = $this->connection
            ->select('id')
            ->from(self::TABLE)
            ->where([
                'namespace' => $names->namespace,
                'name' => $names->name,
                'locale' => $locale
            ])
            ->fetchSingle();

        $content = isset($params['content']) ? $params['content'] : '';

        if ($rowId) {
            // update
            $this->connection
                ->update(self::TABLE, ['content' => $content])
                ->where(['id' => $rowId])
                ->execute();

        } else {
            // new
            $this->connection
                ->insert(self::TABLE, [
                    'namespace' => $names->namespace,
                    'name' => $names->name,
                    'locale' => $locale,
                    'content' => $content
                ])
                ->execute();
        }

        $this->findContentAndFillCache($names->namespace, '', '');
    }
}
