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
//        'dibi'
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

    }

    /**
     * @param Nette\PhpGenerator\ClassType $class
     */
    public function afterCompile(Nette\PhpGenerator\ClassType $class)
    {
        parent::afterCompile($class);

        // TODO
        if ($this->config['storage'] == 'doctrine') {
            /** @var \Nette\PhpGenerator\Method $mappingDriver */
            $mappingDriver = $class
                ->getMethod('createServiceDoctrine__default__driver__Kdyby_Doctrine__annotationsImpl');

            $oldBody = $mappingDriver->getBody();
            $newBody = substr_replace(
                $oldBody,
                '\'' . __DIR__ . '/../Storage\'',
                strrpos($oldBody, 'Entities\',') + strlen('Entities\','),
                0
            );
            $mappingDriver->setBody($newBody);
        }
    }
}
