<?php
/**
 * Created by PhpStorm.
 * User: Glenn
 * Date: 2015-12-21
 * Time: 10:29 AM
 */

namespace TheSlimCollective\Middleware;


use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TheSlimCollective\Helper\BaseMiddleware;

class LoggerMiddleware extends BaseMiddleware
{
    protected $key;

    public function __construct (ContainerInterface $containerInterface, $key = 'logger')
    {
        $this->key = $key;
        parent::__construct($containerInterface);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param callable                                 $next
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke (ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        /** @var $response \Psr\Http\Message\ResponseInterface */
        $response = $next($request, $response);

        /** @var $logger \Monolog\Logger */
        $logger = $this->{$this->key};

        $statusCode = $response->getStatusCode();

        if ($statusCode > 500) {
            $logger->addError($this->convertToString($request));
        } else if ($statusCode > 400) {
            $logger->addWarning($this->convertToString($request));
        } else {
            $logger->addInfo($this->convertToString($request));
        }


        return $response;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return string
     */
    protected function convertToString(ServerRequestInterface $request) {
        $request->getBody()->rewind();
        $structure = [
            "uri" => $request->getUri(),
            "body" => $request->getBody()->getContents()
        ];
        return json_encode($structure);
    }
}