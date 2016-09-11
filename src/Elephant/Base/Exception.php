<?php

namespace Elephant\Base;

use Elephant\Container\Factory;

class Exception
{
    protected $_errorController = "error";
    protected $_errorAction     = "error";

    public function __construct()
    {
    }

    public function setException($e)
    {
        $error              = new \ArrayObject(array(),\ArrayObject::ARRAY_AS_PROPS);
        $exceptionType      = get_class($e);
        $error->exception   = $e;
        $error->type        = $exceptionType;
        Factory::find('Elephant\Base\Request')->setParam('error_handle',$error);
        Factory::find('Elephant\Foundation\Application')->setDispatched(false);
        Factory::find('Elephant\Foundation\Application')->setControllerName($this->getErrorControllerName())
                                          ->setActionName($this->getErrorActionName())
                                          ->dispatch();
        Factory::find('Elephant\Foundation\View')->renderView();
    }

    public function setErrorController($name)
    {
        $this->_errorController = $name;
        return $this;
    }

    public function setErrorAction($name)
    {
        $this->_errorAction = $name;
        return $this;
    }

    public function getErrorControllerName()
    {
        return $this->_errorController;
    }

    public function getErrorActionName()
    {
        return $this->_errorAction;
    }
}