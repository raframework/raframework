<?php
/**
 * User: coderd
 * Date: 2016/1/14
 * Time: 14:38
 */

namespace Ra;

use Ra\Http\Request;
use Ra\Http\Response;
use Ra\Exception\NotFoundException;
use Ra\Exception\MethodNotAllowedException;

class Router
{
    /**
     * Default resource namespace's prefix
     */
    const DEFAULT_RESOURCE_NAMESPACE_PREFIX = "App\\Resource\\";

    /**
     * Default resource name
     * if the request uri equal '/', this resource will be matched.
     */
    const DEFAULT_RESOURCE = 'Index';

    /**
     * Action of list
     */
    const ACTION_OF_LIST = 'lis';

    /**
     * Map of HTTP method and RESTful action
     *
     * @var array
     */
    private $actions = [
        Request::METHOD_POST => 'create',
        Request::METHOD_DELETE => 'delete',
        Request::METHOD_PUT => 'update',
        Request::METHOD_GET => 'get',
        Request::METHOD_PATCH => 'patch',
        Request::METHOD_HEAD => 'head',
        Request::METHOD_OPTIONS => 'options',
    ];

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * checked uri patterns
     * @var array
     */
    private $uriPatterns;

    /**
     * @var string
     */
    private $resourceNamespacePrefix = null;

    /**
     * Class of resource
     *
     * @var string
     */
    private $resourceClass;

    /**
     * Action of resource
     *
     * @var string
     */
    private $resourceAction;

    /**
     * Router constructor.
     * @param Request $request
     * @param Response $response
     * @param $uriPatterns
     * @param string $resourceNamespacePrefix
     */
    public function __construct(Request $request, Response $response, $uriPatterns, $resourceNamespacePrefix = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->checkUriPatterns($uriPatterns);
        $this->withResourceNamespacePrefix($resourceNamespacePrefix);
    }

    public function callResourceAction()
    {
        if (empty($this->resourceClass)) {
            throw new \BadMethodCallException('You should provide the \'resourceClass\' by calling App.matchUriPattern() before App.callResourceAction().');
        }

        $classObject = new $this->resourceClass();

        return call_user_func(array($classObject, $this->resourceAction), $this->request, $this->response);
    }

    public function match()
    {
        $requestUri = trim($this->request->getUri(), '/');
        $uriSegments = explode('/', $requestUri);
        $uriSegmentsCount = count($uriSegments);

        $matched = false;
        foreach ($this->uriPatterns as $pattern => $methods) {
            $patternSegments = explode('/', trim($pattern, '/'));
            $patternSegmentsCount = count($patternSegments);
            if ($patternSegmentsCount != $uriSegmentsCount) {
                continue;
            }

            $args = [];
            $nsSegments = []; // namespace segments
            $matched = true;
            for ($i = 0; $i < $patternSegmentsCount; $i++) {
                if ($patternSegments[$i][0] == ':') {
                    $args[substr($patternSegments[$i], 1)] = $uriSegments[$i];
                } else if ($patternSegments[$i] != $uriSegments[$i]) {
                    $matched = false;
                    break;
                } else {
                    $nsSegments[] = ucfirst($patternSegments[$i]);
                }
            }
            if ($matched) {
                $this->request->withMatchedUriPattern($pattern);
                $this->request->withAttributes($args);
                $method = $this->request->getMethod();
                if (!in_array($method, $methods)) {
                    throw new MethodNotAllowedException($this->request, $this->response, $methods);
                }
                $lastSegmentIsAttribute = $patternSegments[$patternSegmentsCount - 1][0] == ':';
                $this->withResourceClassAndAction($nsSegments, $method, $lastSegmentIsAttribute);
                break;
            }
        }
        if (!$matched) {
            throw new NotFoundException($this->request, $this->response);
        }
    }

    private function withResourceClassAndAction($nsSegments, $method, $lastSegmentIsAttribute)
    {
        // Convert. e.g. email_activation => EmailActivation
        foreach ($nsSegments as $k => $segment) {
            $ss = explode('_', $segment);
            $ucFirst = '';
            foreach ($ss as $s) {
                $ucFirst .= ucfirst($s);
            }
            $nsSegments[$k] = $ucFirst;
        }

        $resourcePath = implode('\\', $nsSegments);
        if ($resourcePath == '') {
            $resourcePath = self::DEFAULT_RESOURCE;
        }
        $class = str_replace('_', '', $this->resourceNamespacePrefix() . $resourcePath);
        if (!class_exists($class)) {
            throw new \RuntimeException("Class '{$class}' is not found");
        }
        $this->resourceClass = $class;

        // While HTTP method is GET, if last segment of uri is attribute,
        // action will be set to be 'get', else to be 'lis'.
        if ($method == 'GET' && !$lastSegmentIsAttribute) {
            $action = self::ACTION_OF_LIST;
        } else {
            $action = $this->actions[$method];
        }
        if (!method_exists($class, $action)) {
            throw new \RuntimeException("Resource action '{$action}' is not found");
        }
        $this->resourceAction = $action;
    }

    private function checkUriPatterns($uriPatterns)
    {
        if (!is_array($uriPatterns)) {
            throw new \InvalidArgumentException("uriPatterns must be an array");
        }
        foreach ($uriPatterns as $pattern => $methods) {
            if (!is_array($methods)) {
                throw new \InvalidArgumentException("Uri pattern's methods must be an array");
            }
            foreach ($methods as $method) {
                if (!array_key_exists($method, $this->actions)) {
                    throw new \InvalidArgumentException("unsupported method ($method)");
                }
            }
        }
        $this->uriPatterns = $uriPatterns;
    }

    private function withResourceNamespacePrefix($resourceNamespacePrefix)
    {
        if ($resourceNamespacePrefix === null) {
            $this->resourceNamespacePrefix = self::DEFAULT_RESOURCE_NAMESPACE_PREFIX;
        } else {
            $this->resourceNamespacePrefix = $resourceNamespacePrefix;
        }
    }

    private function resourceNamespacePrefix()
    {
        return $this->resourceNamespacePrefix;
    }
}