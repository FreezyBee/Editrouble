<?php

namespace FreezyBee\Editrouble\DI;

use FreezyBee\Editrouble\Connector;
use FreezyBee\Editrouble\Storage\Dibi;
use FreezyBee\Editrouble\Storage\Doctrine;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\Schema\Helpers;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;

class EditroubleExtension extends CompilerExtension
{
    /** @var mixed[] */
    protected $defaults = [
        'storage' => null,
        'tableName' => 'editrouble_content',
        'roles' => [
            'editor'
        ],
        'webPaths' => [
            'js' => null,
            'css' => null
        ]
    ];

    /** @var string[] */
    private static array $allowedStorages = ['doctrine', 'dibi'];

    public function loadConfiguration(): void
    {
        /** @var mixed[] $config */
        $config = Helpers::merge($this->getConfig(), $this->defaults);

        Validators::assert($config['storage'], 'string', 'Editrouble - missing storage');
        Validators::assert($config['roles'], 'array', 'Editrouble - invalid roles');

        $builder = $this->getContainerBuilder();

        /** @var FactoryDefinition $def */
        $def = $builder->getDefinition('nette.latteFactory');
        $def->getResultDefinition()
            ->addSetup('?->onCompile[] = function (\Latte\Engine $engine) {
            FreezyBee\Editrouble\Macros::install($engine->getCompiler());
            }', ['@self']);

        $storage = $builder->addDefinition($this->prefix('storage'));

        switch ($config['storage']) {
            case 'dibi':
                $storage->setType(Dibi::class)
                    ->setArgument('tableName', $config['tableName']);
                break;
            case 'doctrine':
                $storage->setType(Doctrine::class);
                break;
            default:
                throw new AssertionException(
                    'Editrouble - invalid storage - it must be (doctrine or dibi)');
        }

        $builder->addDefinition($this->prefix('connector'))
            ->setType(Connector::class)
            ->setArguments([$storage, $config]);
    }
}
