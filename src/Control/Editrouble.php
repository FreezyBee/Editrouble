<?php

namespace FreezyBee\Editrouble\Control;

use FreezyBee\Editrouble\Connector;
use FreezyBee\Editrouble\Storage\IStorage;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Control;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Request;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class Editrouble extends Control
{
    private Connector $connector;
    private IStorage $storage;
    private Request $request;

    public function __construct(Connector $connector, Request $request)
    {
        $this->connector = $connector;
        $this->storage = $connector->getStorage();
        $this->request = $request;
    }

    public function handleSave(): void
    {
        if ($this->connector->checkPermission()) {
            $post = $this->request->getRawBody() ?? '[]';
            $locale = $this->connector->getLocale();

            try {
                $json = Json::decode($post, Json::FORCE_ARRAY);
            } catch (JsonException $e) {
                $this->sendResponse(400, 'invalid json');
                return;
            }

            foreach ($json as $name => $item) {
                if ($locale && !isset($item['locale'])) {
                    $item['locale'] = $locale;
                }

                $this->storage->saveContent($name, $item);
            }
            $this->sendResponse();

        } else {
            $this->sendResponse(403);
        }
    }

    private function sendResponse(int $code = 200, string $data = ''): void
    {
        $this->presenter->sendResponse(
            new CallbackResponse(function (IRequest $request, IResponse $response) use ($code, $data): void {
                $response->setCode($code);
                if ($data) {
                    echo $data;
                }
            })
        );
    }

    public function render(): void
    {
        $config = $this->connector->getConfig();

        $this->template->paths = (object) $config['webPaths'];
        $this->template->userHasPermission = $this->connector->checkPermission();
        $this->template->setFile(__DIR__ . '/../templates/editrouble.latte');
        $this->template->render();
    }
}
