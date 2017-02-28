<?php
/**
 * User: coderd
 * Date: 2017/2/28
 * Time: 10:13
 */

namespace Ra;


use Ra\Http\Request;
use Ra\Http\Response;

interface ProcessorInterface
{
    public function run(Request $request, Response $response);
}