<?php
/**
 * @package ImpressPages
 *
 *
 */

namespace Ip\Internal;

//TODOX move outside Internal
/**
 *
 * Class to get information about current request
 *
 */
class Request
{
    protected $_SERVER = array();
    protected $_POST = array();
    protected $_GET = array();
    protected $_REQUEST = array();




    protected $controllerAction = null;
    protected $controllerClass = null;
    protected $controllerType = null;
    protected $defaultControllerAction = 'index';
    protected $defaultControllerClass = '\\Ip\\Module\\Content\\PublicController';

    const CONTROLLER_TYPE_PUBLIC = 0;
    const CONTROLLER_TYPE_SITE = 1;
    const CONTROLLER_TYPE_ADMIN = 2;


    /**
     * @var \Ip\controller
     */
    protected $controller = null;


    public function __construct()
    {
    }

    public function setPost($post)
    {
        $this->_POST = $post;
        $this->_REQUEST = array_merge($this->_REQUEST, $post);
    }

    public function setServer($server)
    {
        $this->_SERVER = $server;
    }

    public function setGet($get)
    {
        $this->_GET = $get;
        $this->_REQUEST = array_merge($this->_REQUEST, $get);
    }

    public function setRequest($request)
    {
        $this->_REQUEST = $request;
    }


    public function isGet()
    {
        return $this->getMethod() == 'GET';
    }

    public function isPost()
    {
        return $this->getMethod() == 'POST';
    }

    /**
     * @throws \Ip\CoreException
     */
    public function mustBePost()
    {
        if (!$this->isPost()) {
            throw new \Ip\CoreException('POST method required.');
        }
    }

    /**
     * get request method  'GET', 'HEAD', 'POST', 'PUT'.
     * @return string
     */
    public function getMethod()
    {
        return $this->_SERVER['REQUEST_METHOD'];
    }


    /**
     * Returns GET query parameter if $name is passed. Returns all query parameters if name == null.
     *
     * @param string    $name       query parameter name
     * @param mixed     $default    default value if no GET parameter exists
     * @return mixed    GET query variable (all query variables if $name == null)
     */
    public function getQuery($name = null, $default = null)
    {
        return $this->getParam($name, $this->_GET, $default);
    }

    /**
     * Returns POST parameter if $name is passed. Returns all query parameters if name == null.
     *
     * @param string    $name       query parameter name
     * @param mixed     $default    default value if no GET parameter exists
     * @return mixed    GET query variable (all query variables if $name == null)
     */
    public function getPost($name = null, $default = null)
    {
        return $this->getParam($name, $this->_POST, $default);
    }

    /**
     * Returns request parameter if $name is passed. Returns all request parameters if name == null.
     *
     * @param string    $name       query parameter name
     * @param mixed     $default    default value if no GET parameter exists
     * @return mixed    GET query variable (all query variables if $name == null)
     */
    public function getRequest($name = null, $default = null)
    {
        return $this->getParam($name, $this->_REQUEST, $default);
    }

    protected function getParam($name, $values, $default)
    {
        if ($name === null) {
            return $values;
        }
        if (!array_key_exists($name,  $values)) {
            return $default;
        }
        return $values[$name];
    }

    public function getUrl() {
        $pageURL = 'http';
        if (isset($this->_SERVER["HTTPS"]) && $this->_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= '://';
        if ($this->_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $this->_SERVER["SERVER_NAME"].":".$this->_SERVER["SERVER_PORT"].$this->_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $this->_SERVER["SERVER_NAME"].$this->_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    /**
     * @return string path after BASE_URL
     */
    public function getRelativePath()
    {
        $basePath = parse_url(\Ip\Config::baseUrl(''), PHP_URL_PATH);

        if (strpos($this->_SERVER["REQUEST_URI"], $basePath) !== 0) {
            if ($this->_SERVER["REQUEST_URI"] == rtrim($basePath, '/')) {
                return '';
            }
            // TODO log error
            return $this->_SERVER["REQUEST_URI"];
        }

        return substr($this->_SERVER['REQUEST_URI'], strlen($basePath));
    }

    public function fixMagicQuotes()
    {
        if (!get_magic_quotes_gpc()) {
            return;
        }

        $process = array(&$this->_GET, &$this->_POST, &$this->_COOKIE, &$this->_REQUEST);
        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                if (is_array($v)) {
                    $process[$key][stripslashes($k)] = $v;
                    $process[] = & $process[$key][stripslashes($k)];
                } else {
                    $process[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($process);
    }



    public function setController(\Ip\Controller $controller)
    {
        $this->controller = $controller;
    }

    public function getControllerAction()
    {
        if (!$this->controllerAction) {
            $this->parseControllerAction();
        }
        return $this->controllerAction;
    }

    public function getControllerClass()
    {
        if (!$this->controllerClass) {
            $this->parseControllerAction();
        }
        return $this->controllerClass;
    }

    protected function parseControllerAction()
    {
        $action = $this->defaultControllerAction;
        $controllerClass = $this->defaultControllerClass;
        $controllerType = self::CONTROLLER_TYPE_PUBLIC;
        if (sizeof($this->getRequest()) > 0) {
            $actionString = null;
            if(isset($_REQUEST['aa'])) {
                $actionString = $_REQUEST['aa'];
                $controllerClass = 'AdminController';
                $controllerType = self::CONTROLLER_TYPE_ADMIN;
            } elseif(isset($_REQUEST['sa'])) {
                $actionString = $_REQUEST['sa'];
                $controllerClass = 'SiteController';
                $controllerType = self::CONTROLLER_TYPE_SITE;
            } elseif(isset($_REQUEST['pa'])) {
                $actionString = $_REQUEST['pa'];
                $controllerClass = 'PublicController';
                $controllerType = self::CONTROLLER_TYPE_PUBLIC;
            }

            if ($actionString) {
                $parts = explode('.', $actionString);
                $module = array_shift($parts);
                if (isset($parts[0])) {
                    $action = $parts[0];
                }

                $controllerClass = $this->generateControllerClass($module, $controllerType);
            }

        }

        $this->controllerClass = $controllerClass;
        $this->controllerAction = $action;
        $this->controllerType = $controllerType;
    }

    public function getControllerType()
    {
        if ($this->controllerType === null) {
            $this->parseControllerAction();
        }
        return $this->controllerType;
    }

    public function setAction($module, $action, $type)
    {
        if (!in_array($type, array (self::CONTROLLER_TYPE_ADMIN, self::CONTROLLER_TYPE_PUBLIC, self::CONTROLLER_TYPE_SITE))) {
            throw new \Ip\CoreException("Incorrect controller type");
        }
        $this->controllerType = $type;
        $this->controller = null;
        $this->controllerClass = $this->generateControllerClass($module, $type);

        $this->controllerAction = $action;
    }

    private function generateControllerClass($module, $type)
    {
        switch ($type) {
            case self::CONTROLLER_TYPE_ADMIN:
                $className = 'AdminController';
                break;
            case self::CONTROLLER_TYPE_SITE:
                $className = 'SiteController';
                break;
            case self::CONTROLLER_TYPE_PUBLIC:
                $className = 'PublicController';
                break;
        }


        if (in_array($module, \Ip\Module\Plugins\Model::getModules())) {
            $controllerClass = 'Ip\\Module\\'.$module.'\\'.$className;
        } else {
            $controllerClass = 'Plugin\\'.$module.'\\'.$className;
        }
        return $controllerClass;
    }

    public function isDefaultAction()
    {
        return $this->getControllerClass() == $this->defaultControllerClass && $this->getControllerAction() == $this->defaultControllerAction;
    }


}