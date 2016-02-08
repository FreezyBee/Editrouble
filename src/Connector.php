<?php

namespace FreezyBee\Editrouble;

use FreezyBee\Editrouble\Storage\IStorage;
use Nette\Application\UI\Presenter;
use Nette\Object;

/**
 * Class Connector
 * @package FreezyBee\Editrouble
 */
class Connector extends Object
{
    /**
     * @var IStorage
     */
    private $storage;

    /**
     * @var Presenter
     */
    private $presenter;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $locale;

    /**
     * Connector constructor.
     * @param IStorage $storage
     * @param array $config
     */
    public function __construct(IStorage $storage, array $config)
    {
        $this->storage = $storage;
        $this->config = $config;
    }

    /**
     * @param Presenter $presenter
     */
    public function setPresenter(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * @param $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return IStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $name
     * @param array $params
     * @return mixed
     */
    public function getContent($name, array $params = [])
    {
        if ($this->locale && !isset($params['locale'])) {
            $params['locale'] = $this->locale;
        }

        return $this->storage->getContent($name, $params);
    }

    /**
     * @return bool
     */
    public function checkPermission()
    {
        foreach ($this->presenter->user->getRoles() as $role) {
            if (in_array($role, $this->config['roles'])) {
                return true;
            }
        }

        return false;
    }
}
