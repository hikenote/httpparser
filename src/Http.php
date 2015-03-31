<?php
namespace HttpParser;
use HttpParser\Collection;
use HttpParser\Body;
use HttpParser\Parser;

class Http{
    /**
     * The request method
     *
     * @var string
     */
    protected $method;
    /**
     * The request query string params
     *
     * @var array
     */
    protected $queryParams;
    /**
     * The request body object
     *
     * @var \Psr\Http\Message\StreamableInterface
     */
    protected $body;

    /**
     * The request body parsed (if possible) into a PHP array or object
     *
     * @var null|array|object
     */
    protected $bodyParsed;

    /**
     * List of request body parsers (e.g., url-encoded, JSON, XML, multipart)
     *
     * @var callable[]
     */
    protected $bodyParsers = [];
    /**
     * The request headers
     *
     * @var \HttpParser\Collection
     */
    protected $headers;
    /*
     * the body parser
     */
    protected $parsor;
    /**
     * Valid request methods
     *
     * @var string[]
     */
    protected $validMethods = ['CONNECT', 'DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT', 'TRACE'];
    /*
     * create a http request
     */
    function __construct($env=array()){
        $this->body = new Body(fopen('php://input', 'r'));
        if(empty($env)){
            $env = $_SERVER;
        }
        $this->headers = new Collection($env);
        $this->parsor = new Parser();
        //parse application/json
        $this->registerMediaTypeParser('application/json', function ($input) {
            return $this->parsor->jsonParse($input);
        });
        //parse application/xml
        $this->registerMediaTypeParser('application/xml', function ($input) {
            return $this->parsor->xmlParse($input);
        });
        //parse application/x-www-form-urlencoded
        $this->registerMediaTypeParser('application/x-www-form-urlencoded', function ($input) {
            return $this->parsor->urlencodedParse($input);
        });
        //parse multipart/form-data
        $this->registerMediaTypeParser('multipart/form-data', function ($input) {
            return $this->parsor->multipartParse($input, $this->getMethod());
        });
    }
    /**
     * Validate the HTTP method
     *
     * @param  null|string $method
     * @return null|string
     * @throws InvalidArgumentException on invalid HTTP method.
     */
    protected function filterMethod($method)
    {
        if ($method === null) {
            return $method;
        }

        if (is_string($method) === false) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be a string, received %s',
                (is_object($method) ? get_class($method) : gettype($method))
            ));
        }

        $method = strtoupper($method);
        if (in_array($method, $this->validMethods, true) === false) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported HTTP method "%s" provided',
                $method
            ));
        }

        return $method;
    }
    /**
     * Get request content type, if known
     *
     * @return string|null The request content type, minus content-type params
     */
    public function getContentType()
    {
        if(!$this->headers->get('CONTENT_TYPE')){
            return;
        }
        $contentType = $this->headers->get('CONTENT_TYPE');
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        }
        return;
    }
    /**
     * Get the HTTP request method
     *
     * This method returns the HTTP request's method, and it
     * respects override values specified in the `X-Http-Method-Override`
     * request header or in the `_METHOD` body parameter.
     *
     * @return string
     */
    public function getMethod()
    {
        $this->method = $this->headers->get('REQUEST_METHOD');
        if ($this->headers->get('X-Http-Method-Override')) {
            $this->method = $this->filterMethod($this->headers->get('X-Http-Method-Override'));
        } elseif ($this->method === 'POST') {
            if(!empty($_POST['_METHOD'])){
                $this->method = $this->filterMethod($_POST['_METHOD']);
            }
        }
        return $this->method;
    }
    /*
     *Get the GET method params
     */
    public function getQueryParams(){
        if($this->queryParams){
            return $this->queryParams;
        }
        $this->queryParams = $_GET;
        return $this->queryParams;
    }

    /**
     * Retrieve any parameters provided in the request body.
     */
    public function getParsedBody()
    {
        if($this->bodyParsed){
            return $this->bodyParsed;
        }
        if (!$this->body) {
            throw new \InvalidArgumentException('Request body is null');
        }

        $contentType = $this->getContentType();
        $body = (string)$this->body;
        if (isset($this->bodyParsers[$contentType]) === true) {
            $parsed = $this->bodyParsers[$contentType]($body);
            if (!is_null($parsed) && !is_object($parsed) && !is_array($parsed)) {
                throw new \RuntimeException('Request body content type parser return value must be an array, an object, or null');
            }
            $this->bodyParsed = $parsed;
        }

        return $this->bodyParsed;
    }

    /**
     * Register media type parser
     *
     * @param string   $mediaType A HTTP media type (excluding content-type params)
     * @param callable $callable  A callable that returns parsed contents for media type
     */
    public function registerMediaTypeParser($mediaType, callable $callable)
    {
        $callable = $callable->bindTo($this);
        $this->bodyParsers[(string)$mediaType] = $callable;
    }


}