<?php
/**
 * User: coderd
 * Date: 16/1/17
 * Time: 上午11:06
 */

namespace Ra\Exception;

use Ra\Http\Request;
use Ra\Http\Response;

class MethodNotAllowedException extends RaException
{
    /**
     * HTTP methods allowed
     *
     * @var string[]
     */
    protected $allowedMethods;

    /**
     * Create new exception
     *
     * @param Request $request
     * @param Response $response
     * @param string[] $allowedMethods
     */
    public function __construct(Request $request, Response $response, array $allowedMethods)
    {
        parent::__construct($request, $response);
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @return string[]
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }

}