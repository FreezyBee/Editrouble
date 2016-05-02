<?php

namespace FreezyBee\Editrouble\Control;

use FreezyBee\Editrouble\Connector;
use Nette\Http\Request;

/**
 * Class EditroubleTrait
 * @package FreezyBee\Editrouble\Control
 */
trait EditroubleTrait
{
    /** @var Connector */
    public $editroubleConnector;

    /** @var Request @inject */
    public $rawRequest;
    
    /**
     * @param Connector $connector
     */
    public function injectConnector(Connector $connector)
    {
        $this->editroubleConnector = $connector;
        $this->editroubleConnector->setPresenter($this);
    }

    /**
     * @param $name
     * @return Editrouble
     */
    protected function createComponentEditrouble($name)
    {
        $control = new Editrouble($this, $name, $this->editroubleConnector, $this->rawRequest);
        return $control;
    }
}
