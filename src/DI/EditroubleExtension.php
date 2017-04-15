<?php

namespace FreezyBee\Editrouble\DI;

use Kdyby\Doctrine\DI\IEntityProvider;
use Nette\DI\CompilerExtension;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;
use FreezyBee\Editrouble\Connector;

if (!interface_exists('Kdyby\Doctrine\DI\IEntityProvider')) {
    class_alias('FreezyBee\Editrouble\DI\IFakeEntityProvider', 'Kdyby\Doctrine\DI\IEntityProvider');
}

/**
 * Class EditroubleExtension
 * @package FreezyBee\MailChimp\DI
 */
class EditroubleExtension extends CompilerExtension implements IEntityProvider
{
    /**
     * @var array
     */
    private $defaults = [
        'storage' => null,
        'roles' => [
            'editor'
        ],
        'webPaths' => [
            'js' => null,
            'css' => null
        ]
    ];

    /**
     * @var array
     */
    private static $allowedStorages = [
        'doctrine',
//        'ndb',
        'dibi'
    ];

    /**
     * @throws AssertionException
     */
    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);

        Validators::assert($config['storage'], 'string', 'Editrouble - missing storage');

        if (!in_array($config['storage'], self::$allowedStorages, true)) {
            throw new AssertionException('Editrouble - invalid storage - it must be (' .
                implode(' OR ', self::$allowedStorages) . ')');
        }

        Validators::assert($config['roles'], 'array', 'Editrouble - invalid roles');

        $builder = $this->getContainerBuilder();
        $builder->getDefinition('nette.latteFactory')
            ->addSetup('?->onCompile[] = function (\Latte\Engine $engine) {
            FreezyBee\Editrouble\Macros::install($engine->getCompiler());
            }', ['@self']);

        $storage = $builder->addDefinition($this->prefix('storage'))
            ->setClass('FreezyBee\Editrouble\Storage\\' . ucfirst($config['storage']));

        $builder->addDefinition($this->prefix('connector'))
            ->setClass(Connector::class)
            ->setArguments([$storage, $config]);
    }

    /**
     * Returns associative array of Namespace => mapping definition
     * @return array
     */
    public function getEntityMappings()
    {
        return ['FreezyBee\Editrouble\Storage' => __DIR__ . '/../Storage/'];
    }
}
