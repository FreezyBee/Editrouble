<?php

namespace FreezyBee\Editrouble\Storage;

use Doctrine\ORM\EntityManagerInterface;
use Nette\Caching;

class Doctrine extends BaseStorage implements IStorage
{
    private EntityManagerInterface $entityManager;

    public function __construct(Caching\IStorage $storage, EntityManagerInterface $entityManager)
    {
        parent::__construct($storage);
        $this->entityManager = $entityManager;
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

        return $tmp[$namespace][$name][$locale] ?? '';
    }

    private function findContentEntity(string $namespace, string $name, string $locale): ?DoctrineEntity
    {
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

    public function saveContent(string $name, array $params): void
    {
        $names = $this->decodeNames($name);
        $locale = $params['locale'] ?? '';

        $entity = $this->findContentEntity($names->namespace, $names->name, $locale);
        $content = $params['content'] ?? '';

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

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
