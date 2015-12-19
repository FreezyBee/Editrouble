<?php

namespace FreezyBee\Editrouble\Control;

use FreezyBee\Editrouble\Connector;
use FreezyBee\Editrouble\Storage\IStorage;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;

/**
 * Class Editrouble
 * @package FreezyBee\Editrouble\Control
 */
class Editrouble extends Control
{
    /** @var Connector */
    private $connector;

    /** @var IStorage */
    private $storage;

    /**
     * Editrouble constructor.
     * @param IContainer $parent
     * @param string $name
     * @param Connector $connector
     */
    public function __construct(IContainer $parent, $name, Connector $connector)
    {
        parent::__construct($parent, $name);
        $this->connector = $connector;
        $this->storage = $connector->getStorage();
    }

    /**
     *
     */
    public function handleSave()
    {
        $presenter = $this->getPresenter();

        if ($presenter->user->isInRole('editor')) {
            $request = $this->getPresenter()->getRequest();
            $post = $request->getPost();

            foreach ($post as $name => $item) {
                $this->storage->saveContent($name, $item);
            }
            $presenter->sendJson(['status' => 0]);

        } else {
            $presenter->sendJson(['status' => -1]);
        }
    }

    /**
     *
     */
    public function render()
    {
        $config = $this->connector->getConfig();

        $this->template->paths = (object) $config['webPaths'];
        $this->template->setFile(__DIR__ . '/../templates/editrouble.latte');
        $this->template->render();
    }
}
