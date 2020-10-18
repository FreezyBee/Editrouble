<?php

namespace FreezyBee\Editrouble;

use FreezyBee\Editrouble\Storage\IStorage;
use Nette\Application\UI\Presenter;
use Nette\SmartObject;

class Connector
{
    use SmartObject;

    private IStorage $storage;

    private Presenter $presenter;

    /** @var mixed[] */
    private array $config;

    private string $locale;

    /**
     * @param mixed[] $config
     */
    public function __construct(IStorage $storage, array $config)
    {
        $this->storage = $storage;
        $this->config = $config;
    }

    public function setPresenter(Presenter $presenter): void
    {
        $this->presenter = $presenter;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getStorage(): IStorage
    {
        return $this->storage;
    }

    /**
     * @return mixed[]
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param mixed[] $params
     */
    public function getContent(string $name, array $params = []): string
    {
        if ($this->locale && !isset($params['locale'])) {
            $params['locale'] = $this->locale;
        }

        return $this->storage->getContent($name, $params);
    }

    public function checkPermission(): bool
    {
        foreach ($this->presenter->user->getRoles() as $role) {
            if (in_array($role, $this->config['roles'])) {
                return true;
            }
        }

        return false;
    }
}
