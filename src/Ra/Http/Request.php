<?php
/**
 * User: huangshaowen
 * Date: 2016/1/13
 * Time: 12:03
 */

namespace Ra\Http;

use Ra\Exception\BadBodyException;
use Ra\Exception\UnsupportedMediaType;

class Request
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    private $method;

    private $uri;

    /**
     * The server environment variables at the time the request was created.
     *
     * @var array
     */
    protected $serverParams;

    /**
     * The request query string params
     *
     * @var array
     */
    protected $queryParams;

    /**
     * The request body parsed (if possible) into a PHP array
     *
     * @var null|array
     */
    protected $bodyParsed;

    /**
     * List of request body parsers (e.g., url-encoded, JSON, XML, multipart)
     *
     * @var callable[]
     */
    protected $bodyParsers = [];

    private $attributes = [];

    private $matchedUriPattern;

    public function __construct()
    {
        $this->registerMediaTypeParser('application/json', function ($input) {
            if (empty($input)) {
                return [];
            }
            $body = json_decode($input, true);
            if ($body === null) {
                throw new BadBodyException('Body should be a JSON object');
            }
            return $body;
        });
    }

    public function getUri()
    {
        if ($this->uri === null) {
            $uriSegments = parse_url($this->getServerParams()['REQUEST_URI']);
            if ($uriSegments && $uriSegments['path']) {
                $this->uri = $uriSegments['path'];
            } else {
                $this->uri = '';
            }
        }
        return $this->uri;
    }

    public function getMethod()
    {
        if ($this->method === null) {
            $this->method = $this->getServerParams()['REQUEST_METHOD'];
        }
        return $this->method;
    }

    public function getServerParams()
    {
        if ($this->serverParams === null) {
            $this->serverParams = $_SERVER;
        }
        return $this->serverParams;
    }

    public function getQueryParams()
    {
        if ($this->queryParams === null) {
            $this->queryParams = $_GET;
        }
        return $this->queryParams;
    }

    public function getBody()
    {
        return file_get_contents('php://input');
    }

    public function getParsedBody()
    {
        if ($this->bodyParsed === null) {
            $mediaType = $this->getMediaType();
            if (!isset($this->bodyParsers[$mediaType])) {
                throw new UnsupportedMediaType('Unsupported media type \'' . $mediaType . '\'');
            }

            $body = (string)$this->getBody();
            $parsed = $this->bodyParsers[$mediaType]($body);
            $this->bodyParsed = $parsed;
        }
        return $this->bodyParsed;
    }

    public function withAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getMatchedUriPattern()
    {
        return $this->matchedUriPattern;
    }

    public function withMatchedUriPattern($matchedUriPattern)
    {
        $this->matchedUriPattern = $matchedUriPattern;
    }

    public function getContentType()
    {
        return $this->getServerParams()['HTTP_CONTENT_TYPE'];
    }

    /**
     * Get request media type, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request media type, minus content-type params
     */
    public function getMediaType()
    {
        $contentType = $this->getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        }

        return null;
    }

    /**
     * Register media type parser.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string   $mediaType A HTTP media type (excluding content-type
     *     params).
     * @param callable $callable  A callable that returns parsed contents for
     *     media type.
     */
    public function registerMediaTypeParser($mediaType, callable $callable)
    {
        $this->bodyParsers[(string)$mediaType] = $callable;
    }
}