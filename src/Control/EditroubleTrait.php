<?php

namespace FreezyBee\Editrouble\Control;

use FreezyBee\Editrouble\Connector;
use Nette\Http\Request;

trait EditroubleTrait
{
    /** @var Connector */
    public $editroubleConnector;

    /** @var Request @inject */
    public $rawRequest;
    
    public function injectConnector(Connector $connector): void
    {
        $this->editroubleConnector = $connector;
        $this->editroubleConnector->setPresenter($this);
    }

    protected function createComponentEditrouble(): Editrouble
    {
        return new Editrouble($this->editroubleConnector, $this->rawRequest);
    }
}
