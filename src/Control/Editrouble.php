<?php

namespace FreezyBee\Editrouble\Control;

use FreezyBee\Editrouble\Connector;
use FreezyBee\Editrouble\Storage\IStorage;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

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

    /** @var Request */
    private $request;

    /**
     * Editrouble constructor.
     * @param IContainer $parent
     * @param string $name
     * @param Connector $connector
     */
    public function __construct(IContainer $parent, $name, Connector $connector, Request $request)
    {
        parent::__construct($parent, $name);
        $this->connector = $connector;
        $this->storage = $connector->getStorage();
        $this->request = $request;
    }

    /**
     *
     */
    public function handleSave()
    {
        if ($this->connector->checkPermission()) {
            $post = $this->request->getRawBody();
            $locale = $this->connector->getLocale();

            try {
                $json = Json::decode($post);
            } catch (JsonException $e) {
                $this->sendResponse(400, 'invalid json');
                return;
            }

            foreach ($json as $name => $item) {
                if ($locale && !isset($item->locale)) {
                    $item->locale = $locale;
                }

                $this->storage->saveContent($name, $item);
            }
            $this->sendResponse();

        } else {
            $this->sendResponse(403);
        }
    }

    /**
     * @param int $code
     * @param string $data
     */
    private function sendResponse($code = 200, $data = '')
    {
        $this->presenter->sendResponse(
            new CallbackResponse(function (Request $request, Response $response) use ($code, $data) {
                $response->setCode($code);
                if ($data) {
                    echo $data;
                }
            })
        );

        $this->presenter->terminate();
    }

    /**
     *
     */
    public function render()
    {
        $config = $this->connector->getConfig();

        $this->template->paths = (object)$config['webPaths'];
        $this->template->userHasPermission = $this->connector->checkPermission();
        $this->template->setFile(__DIR__ . '/../templates/editrouble.latte');
        $this->template->render();
    }
}
