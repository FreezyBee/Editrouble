<?php

namespace FreezyBee\Editrouble\Storage;

use Dibi\Connection;
use Dibi\DriverException;
use Nette\Caching;

class Dibi extends BaseStorage implements IStorage
{
    private Connection $connection;
    private string $tableName;

    public function __construct(string $tableName, Caching\IStorage $storage, Connection $connection)
    {
        parent::__construct($storage);
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    public function getContent(string $name, array $params): string
    {
        $names = $this->decodeNames($name);
        $locale = $params['locale'] ?? '';

        $records = $this->loadCachedNamespace($names->namespace);

        return $records[$names->namespace][$names->name][$locale] ?? $this->findContentAndFillCache($names->namespace, $names->name, $locale);
    }

    private function findContentAndFillCache(string $namespace, string $name, string $locale): string
    {
        try {
            $result = $this->connection
                ->select('*')
                ->from($this->tableName)
                ->where(['namespace%s' => $namespace])
                ->fetchAll();
        } catch (DriverException $e) {
            // check table not exist
            if ($e->getCode() == 1146) {
                $this->connection
                    ->query('
                      CREATE TABLE %n (
                      [id] int(11) NOT NULL AUTO_INCREMENT,
                      [namespace] varchar(255) NOT NULL,
                      [name] varchar(255) NOT NULL,
                      [locale] varchar(255) NOT NULL,
                      [content] text NOT NULL,
                      PRIMARY KEY ([id]),
                      UNIQUE KEY [uniq_record] ([namespace],[name],[locale])
                    );', $this->tableName);

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

        return $tmp[$namespace][$name][$locale] ?? '';
    }

    public function saveContent(string $name, array $params): void
    {
        $names = $this->decodeNames($name);
        $locale = $params['locale'] ?? '';

        $rowId = $this->connection
            ->select('id')
            ->from($this->tableName)
            ->where([
                'namespace' => $names->namespace,
                'name' => $names->name,
                'locale' => $locale
            ])
            ->fetchSingle();

        $content = $params['content'] ?? '';

        if ($rowId) {
            // update
            $this->connection
                ->update($this->tableName, ['content' => $content])
                ->where(['id' => $rowId])
                ->execute();

        } else {
            // new
            $this->connection
                ->insert($this->tableName, [
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
