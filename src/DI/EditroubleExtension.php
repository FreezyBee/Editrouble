<?php

namespace FreezyBee\Editrouble\DI;

use Nette;
use Nette\DI\CompilerExtension;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;

/**
 * Class EditroubleExtension
 * @package FreezyBee\MailChimp\DI
 */
class EditroubleExtension extends CompilerExtension
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

        if (!in_array($config['storage'], self::$allowedStorages)) {
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
            ->setClass('FreezyBee\Editrouble\Connector')
            ->setArguments([$storage, $config]);


        if ($config['storage'] == 'doctrine') {
            $serviceName = 'doctrine.default.driver.FreezyBee.annotationsImpl';

            $builder->addDefinition($serviceName)
                ->setClass('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver')
                ->setFactory('Kdyby\Doctrine\Mapping\AnnotationDriver', [
                    [__DIR__ . '/../Storage'],
                    '@annotations.reader',
                    '@doctrine.cache.default.metadata'
                ])
                ->setAutowired(false)
                ->setInject(false);

            $builder->getDefinition('doctrine.default.metadataDriver')
                ->addSetup('addDriver', ['@' . $serviceName, 'FreezyBee\Editrouble\Storage']);

        } elseif ($config['storage'] == 'dibi') {
            // TODO ?
        }
    }
}
