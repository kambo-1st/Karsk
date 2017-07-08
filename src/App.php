<?php

namespace Kambo\Matryoshka;

use Kambo\Matryoshka\Route\RouteBuilder;

use Kambo\Router\Route\Collection as RouteCollection;
/*use Kambo\Router\Dispatcher\DispatcherClosure;
use Kambo\Router\Dispatcher\DispatcherClosurePsr;*/
use Kambo\Matryoshka\Routing\Dispatcher\DispatcherClosurePsr;
use Kambo\Router\Matcher\Regex;

// \Http\Message
use Kambo\Http\Message\Environment\Environment;
use Kambo\Http\Message\Stream;

// \Http\Message\Factories
use Kambo\Http\Message\Factories\Environment\ServerRequestFactory;
use Kambo\Http\Message\Response;

/**
 *
 *
 * @package Kambo\Matryoshka
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class App
{
    /**
     * Route collection
     *
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * Create new Uri.
     *
     * @param string $scheme   Uri scheme.
     *
     */
    public function __construct(
        $settings = []
    ) {
        $routeBuilder          = new RouteBuilder();
        $this->routeCollection = new RouteCollection($routeBuilder);
    }

    /**
     * Add route matched with GET method.
     * Shortcut for createRoute function with preset GET method.
     *
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url match
     *                       the route
     *
     * @return self for fluent interface
     */
    public function get($route, $handler)
    {
        return $this->routeCollection->get($route, $handler);
    }

    /**
     * Add route matched with POST method.
     * Shortcut for createRoute function with preset POST method.
     *
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url match
     *                       the route
     *
     * @return self for fluent interface
     */
    public function post($route, $handler)
    {
        return $this->routeCollection->post($route, $handler);
    }

    /**
     * Add route matched with DELETE method.
     * Shortcut for createRoute function with preset DELETE method.
     *
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url match
     *                       the route
     *
     * @return self for fluent interface
     */
    public function delete($route, $handler)
    {
        return $this->routeCollection->delete($route, $handler);
    }

    /**
     * Add route matched with PUT method.
     * Shortcut for createRoute function with preset PUT method.
     *
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url match
     *                       the route
     *
     * @return self for fluent interface
     */
    public function put($route, $handler)
    {
        return $this->routeCollection->put($route, $handler);
    }

    /**
     * Add route that will be matched to any method.
     * Shortcut for createRoute function with preset ANY method.
     *
     * @param mixed $route   route definition
     * @param mixed $handler handler that will be executed if the url match
     *                       the route
     *
     * @return self for fluent interface
     */
    public function any($route, $handler)
    {
        return $this->routeCollection->any($route, $handler);
    }

    private $callablePipeline;

    /**
     * Add middleware
     *
     * Prepends new middleware to the app's middleware stack.
     *
     * @param  callable|string $callable The callback routine
     *
     * @return static
     */
    public function add($callable)
    {

        if (is_null($this->callablePipeline)) {
            $this->addRouterMiddleware();
        }

        $next = end($this->callablePipeline);
        $this->callablePipeline[] = function (
            $request,
            $response
        ) use (
            $callable,
            $next
        ) {
            $result = call_user_func($callable, $request, $response, $next);

            return $result;
        };
    }

    private function addRouterMiddleware()
    {
        $router = function ($request, $response) {
            $response->getBody()->write('<br>ROUTER');

            $dispatcherClosure = new DispatcherClosurePsr();
            $matcher           = new Regex($this->routeCollection, $dispatcherClosure);

            //$routerResult = $matcher->match($request, [$response]);

            $matchedRoute = $matcher->matchRequest($request);
            if ($matchedRoute) {
                $routeMiddlewares = $matchedRoute->getMiddleware();
                if (!empty($routeMiddlewares)) {
                    $middlewarePipeline = [];

                    $firstRun = true;
                    $router = function ($request, $response) {
                        $dispatcherClosure = $request->getAttribute('dispatcher');
                        $matchedRoute = $request->getAttribute('matchedRoute');

                        $routerResult = $dispatcherClosure->dispatchRoute($matchedRoute, [$request, $response]);

                        return $routerResult;
                    };

                    foreach ($routeMiddlewares as $middleware) {
                        if ($firstRun) {
                            $middlewarePipeline[] = function (
                                $request,
                                $response
                            ) use (
                                $middleware,
                                $router
                            ) {
                                $result = call_user_func($middleware, $request, $response, $router);

                                return $result;
                            };
                        } else {
                            $next = end($middlewarePipeline);
                            $middlewarePipeline[] = function (
                                $request,
                                $response
                            ) use (
                                $middleware,
                                $next
                            ) {
                                $result = call_user_func($middleware, $request, $response, $next);

                                return $result;
                            };
                        }

                        $firstRun = false;
                    }


                    $request = $request->withAttribute('dispatcher', $dispatcherClosure);
                    $request = $request->withAttribute('matchedRoute', $matchedRoute);

                    $start        = end($middlewarePipeline);
                    $routerResult = $start($request, $response);
                } else {
                    $routerResult = $dispatcherClosure->dispatchRoute($matchedRoute, [$request, $response]);
                }
            }

            $responseNEW = $this->marshalRouterResult($routerResult, $response);


            $response->getBody()->write("<br>ROUTER AFTER");

            return $responseNEW;
        };

        $this->callablePipeline[] = function (
            $request,
            $response
        ) use (
            $router
        ) {
            $result = call_user_func($router, $request, $response);

            return $result;
        };
    }

    private function executePipeline($request, $response/*, $router*/)
    {
        $start = end($this->callablePipeline);
        $response = $start($request, $response);
        return $response;
    }

    /**
     * Run application
     *
     * @return void
     */
    public function run()
    {
        $response = new Response();

        // Create Environment object based on server variables.
        $environment = new Environment($_SERVER, fopen('php://input', 'w+'), $_POST, $_COOKIE, $_FILES);
        // Create instance of ServerRequest object
        $request = (new ServerRequestFactory())->create($environment);

        $response = $this->executePipeline($request, $response);

        $this->renderResponse($response);
    }

    // ------------ PRIVATE METHODS

    private function marshalRouterResult($routerResult, $response)
    {
        if (!($routerResult instanceof Response)) {
            // do marsahling
            $encoded = '';
            if (is_array($routerResult)) {
                $encoded = json_encode($routerResult);
            } elseif (is_scalar($routerResult)) {
                $encoded = (string)$routerResult;
            } elseif (is_object($routerResult)) {
                if (method_exists($routerResult, '__toString')) {
                    $encoded = (string)$routerResult;
                } else {
                    // scream...
                }
            } elseif (is_callable($routerResult)) {
                $encoded = new CallbackStream($routerResult);
            }

            $response->getBody()->write($encoded);
            //$response = new Response(200, [], $encoded);
        } else {
            // merge it...
            $response->getBody()->write((string)$routerResult->getBody());
        }

        return $response;
    }


    private function marshalResult($response)
    {
        if (!($response instanceof Response)) {
            // do marsahling
            $encoded = '';
            if (is_array($response)) {
                $encoded = json_encode($response);
            } elseif (is_scalar($response)) {
                $encoded = (string)$response;
            } elseif (is_object($response)) {
                if (method_exists($response, '__toString')) {
                    $encoded = (string)$response;
                } else {
                    // scream...
                }
            } elseif (is_callable($response)) {
                $encoded = new CallbackStream($response);
            }

            $response = new Response(200, [], $encoded);
        }

        return $response;
    }

    // RESULT RENDERER

    private function renderResponse(Response $response)
    {
            $statusHeader = $this->getStatusCodeHeader($response);
            $body         = $response->getBody();

            $this->renderHeader($statusHeader);
            $this->renderBody($body);
    }

    private function renderHeader($header, $replace = true)
    {
        header($header, $replace);
    }

    private function renderBody($body)
    {
        echo $body;
    }

    private function getStatusCodeHeader(Response $response)
    {
        $httpStatusCode = $response->getStatusCode();
        $httpStatusMsg  = $response->getReasonPhrase();
        $phpSapiName    = substr(php_sapi_name(), 0, 3);

        if ($phpSapiName == 'cgi' || $phpSapiName == 'fpm') {
            return 'Status: '.$httpStatusCode.' '.$httpStatusMsg;
        } else {
            $protocol = 'HTTP/'.$response->getProtocolVersion();
            return $protocol.' '.$httpStatusCode.' '.$httpStatusMsg;
        }
    }

    /**
     * Url encode
     *
     * This method percent-encodes all reserved
     * characters in the provided path string. This method
     * will NOT double-encode characters that are already
     * percent-encoded.
     *
     * @param  string $baz The raw uri path.
     *
     * @return string The RFC 3986 percent-encoded uri path.
     *
     * @link   http://www.faqs.org/rfcs/rfc3986.html
     */
    private function fooBar($baz)
    {
    }
}
