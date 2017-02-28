<?php
/**
 * User: coderd
 * Date: 2017/2/28
 * Time: 14:32
 */

namespace Ra;


use Ra\Http\Request;
use Ra\Http\Response;

interface ExceptionHandlerInterface
{
    public function handle(\Exception $e, Request $request, Response $response);
}