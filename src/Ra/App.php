<?php
/**
 * User: huangshaowen
 * Date: 2016/1/14
 * Time: 10:43
 */

namespace Ra;

use Ra\Http\Request;
use Ra\Http\Response;
use Ra\Exception\NotFoundException;
use Ra\Exception\MethodNotAllowedException;

class App
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * Exception handler
     *
     * @var callable
     */
    private $exceptionHandler;

    /**
     * App constructor.
     * @param array $uriPatterns
     * @param string $resourceNamespacePrefix
     */
    public function __construct($uriPatterns, $resourceNamespacePrefix = null)
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response, $uriPatterns, $resourceNamespacePrefix);
    }

    public function matchUriPattern()
    {
        if ($this->exception === null) {
            try {
                $this->router->match();
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }
        return $this;
    }

    public function callResourceAction()
    {
        if ($this->exception === null) {
            try {
                $this->router->callResourceAction();
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }
        return $this;
    }

    public function call($callable, $ignoreException = false)
    {
        if ($ignoreException || $this->exception === null) {
            try {
                call_user_func($callable, $this->request, $this->response);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }
        return $this;
    }

    public function respond()
    {
        if (!headers_sent()) {
            // Status
            header(sprintf(
                'HTTP/%s %s %s',
                $this->response->getProtocolVersion(),
                $this->response->getStatusCode(),
                $this->response->getReasonPhrase()
            ));

            // Headers
            foreach ($this->response->getHeaders() as $name => $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        $body = $this->response->getBody();
        if ($body) {
            echo $body;
        }

        return $this;
    }

    public function withExceptionHandler(callable $handler)
    {
        $this->exceptionHandler = $handler;
    }

    private function handleException(\Exception $e)
    {
        $this->exception = $e;
        if ($this->exceptionHandler) {
            try {
                return call_user_func($this->exceptionHandler, $e, $this->request, $this->response);
            } catch (\Exception $residualException) {
                $this->sysHandleException($residualException);
            }
        } else {
            $this->sysHandleException($e);
        }
    }

    private function sysHandleException(\Exception $e)
    {
        if ($e instanceof MethodNotAllowedException) {
            $this->response->withStatus(405);
            $this->response->withHeader('Allow', implode(', ', $e->getAllowedMethods()));
        } else if ($e instanceof NotFoundException) {
            $this->response->withStatus(404);
        } else {
            $this->response->withStatus(500);
            trigger_error(
                'Unhandled exception \'' . get_class($e)  .'\' with message \'' . $e->getMessage() . '\''
                . ' in ' . $e->getFile() . ':' . $e->getLine() . "\nStack trace:\n" . $e->getTraceAsString(),
                E_USER_WARNING
            );
        }
    }
}