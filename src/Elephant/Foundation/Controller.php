<?php

namespace Elephant\Foundation;

use Elephant\Base\Ensure;
use Elephant\Container\Factory;

class Controller
{
    public function __construct()
    {
        $this->init();
    }

    public function dispatch($action)
    {
        // #warning 还没考虑其他输出是否要过滤输出内容 Json输出应该作为一个Json类来处理Hrader Etag
        $this->preDispatch();
        $data = $this->$action();
        if ($data instanceof View) {
            $data->render(null, null, true);
        } else if (is_array($data) || is_object($data)) {
            header("content-type:application/json;charset=utf-8");
            $data = json_encode($data);
            $etag = md5($data);
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
                header("HTTP/1.1 304 Not Modified");
            } else {
                header("Etag: ".$etag);
                echo $data;
            }
        } else if (is_string($data) || is_numeric($data)) {
            echo $data;
        }
        $this->postDispatch();
    }

    public function init()
    {
    }

    public function preDispatch()
    {
    }

    public function postDispatch()
    {
    }

    public static function htmlspecialcharsRecursive($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        if (is_string($value)) {
            return htmlspecialchars($value);
        }
        if (is_array($value)) {
            foreach ($value as $k=>$v) {
                $value[$k] = self::htmlspecialcharsRecursive($v);
            }
            return $value;
        }
        if (is_object($value)) {
            foreach ($value as $k=>$v) {
                $value->$k = self::htmlspecialcharsRecursive($v);
            }
            return $value;
        }
        return $value;
    }

    public function assign($spec, $value = null, $dohtmlspecialchars = true)
    {
        if (is_string($spec)) {
            Ensure::ensureFalse('_' == substr($spec, 0, 1),"Setting private or protected class members is not allowed");
            if ($dohtmlspecialchars) {
                $value = self::htmlspecialcharsRecursive($value);
            }
            Factory::find('Elephant\Foundation\View')->$spec = $value;
        } elseif (is_array($spec)) {
            //TODO if(is_array($val))
            foreach ($spec as $key=>$val) {
                Ensure::ensureFalse('_' == substr($key, 0, 1),"Setting private or protected class members is not allowed");
                if (is_string($val)) {
                    Factory::find('Elephant\Foundation\View')->$key = $dohtmlspecialchars ? htmlspecialchars($val) : $val;
                } else {
                    if ($dohtmlspecialchars) {
                        $val = self::htmlspecialcharsRecursive($val);
                    }
                    Factory::find('Elephant\Foundation\View')->$key = $val;
                }
            }
        }
    }

    public function setNoViewRender($flag)
    {
        return Factory::find('Elephant\Foundation\View')->setNoRender($flag);
    }

    public function getControllerName()
    {
        return Application::$curController;
    }

    public function getActionName()
    {
        return Application::$curAction;
    }

    public function getParam($key, $default = null)
    {
        $value = Factory::find('Elephant\Base\Request')->get($key);

        return (null==$value && null !== $default) ? $default : $value;
    }

    public function getRequest($key = null, $default = null)
    {
        $value = Factory::find('Elephant\Base\Request')->getRequest($key, $default);

        return $value;
    }

    public function getPost($key = null, $default = null)
    {
        $value = Factory::find('Elephant\Base\Request')->getPost($key, $default);

        return $value;
    }

    public function render($name = null, $noController = false)
    {
        if (is_null($name)) return;
        Factory::find('Elephant\Foundation\View')->setControllerRender(true);
        return Factory::find('Elephant\Foundation\View')->render($name, $noController);
    }

    public function fetch($name = null, $noController = false)
    {
        if (is_null($name)) return;
        Factory::find('Elephant\Foundation\View')->setControllerRender(true);
        return Factory::find('Elephant\Foundation\View')->fetch($name, $noController);
    }

    public function setViewSuffix($suffix)
    {
        if (empty($suffix)) return false;
        Factory::find('Elephant\Foundation\View')->setViewSuffix($suffix);
    }

    public function _forward($action, $controller = null)
    {
        if (null !== $controller) {
            Factory::find('Elephant\Foundation\Application')->setControllerName($controller);
        }
        Factory::find('Elephant\Foundation\Application')->setActionName($action);
        Factory::find('Elephant\Foundation\Application')->setDispatched(false);
        Factory::find('Elephant\Foundation\Application')->dispatch();
    }

    public function initView()
    {
        $this->view = Factory::find('Elephant\Foundation\View');
    }

}