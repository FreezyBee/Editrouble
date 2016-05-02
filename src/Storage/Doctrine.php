<?php

namespace FreezyBee\Editrouble\Storage;

use Doctrine\ORM\Query;
use Kdyby\Doctrine\EntityManager;
use Nette\Caching;

/**
 * Class Doctrine
 * @package FreezyBee\Editrouble\Storage
 */
class Doctrine extends BaseStorage implements IStorage
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Doctrine constructor.
     * @param Caching\IStorage $storage
     * @param EntityManager $entityManager
     */
    public function __construct(Caching\IStorage $storage, EntityManager $entityManager)
    {
        parent::__construct($storage);
        $this->entityManager = $entityManager;
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
     */
    private function findContentAndFillCache($namespace, $name, $locale)
    {
        /** @var DoctrineEntity[] $result */
        $result = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(DoctrineEntity::class, 'c')
            ->where('c.namespace = :namespace')
            ->setParameters([
                'namespace' => $namespace
            ])
            ->getQuery()
            ->getResult();

        $tmp = [];

        foreach ($result as $item) {
            if (!isset($tmp[$item->getName()])) {
                $tmp[$item->getName()] = [];
            }

            $tmp[$item->getNamespace()][$item->getName()][$item->getLocale()] = $item->getContent();
        }

        $this->saveCachedNamespace($namespace, $tmp);

        return isset($tmp[$namespace][$name][$locale]) ? $tmp[$namespace][$name][$locale] : '';
    }

    /**
     * @param $namespace
     * @param $name
     * @param $locale
     * @return DoctrineEntity
     */
    private function findContentEntity($namespace, $name, $locale)
    {
        /** @var DoctrineEntity[] $result */
        return $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(DoctrineEntity::class, 'c')
            ->where('c.namespace = :namespace')
            ->andWhere('c.name = :name')
            ->andWhere('c.locale = :locale')
            ->setParameters([
                'namespace' => $namespace,
                'name' => $name,
                'locale' => $locale
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $name
     * @param $params
     */
    public function saveContent($name, $params)
    {
        $names = $this->decodeNames($name);
        $locale = (isset($params->locale)) ? $params->locale : '';

        $entity = $this->findContentEntity($names->namespace, $names->name, $locale);
        $content = isset($params->content) ? $params->content : '';

        if ($entity) {
            // update
            $entity->setContent($content);
        } else {
            // new
            $names = $this->decodeNames($name);
            $entity = new DoctrineEntity(
                $names->namespace,
                $names->name,
                $locale,
                $content
            );
        }

        $this->findContentAndFillCache($names->namespace, '', '');

        $this->entityManager->persist($entity)->flush();
    }
}
