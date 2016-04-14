<?php
/**
 * User: coderd
 * Date: 16/1/17
 * Time: 上午11:00
 */

namespace Ra\Exception;

use Ra\Http\Request;
use Ra\Http\Response;

class RaException extends \Exception
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Create new exception
     *
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct();
        $this->request = $request;
        $this->request = $response;
    }

    /**
     * Get request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}