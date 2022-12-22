<?php

declare(strict_types=1);

namespace App;

use DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

trait HttpRequest
{
    /**
     * @param string[] $headers
     * @param string[] $cookies
     * @param string[][] $query
     */
    public function get(string $uri, array $headers = [], array $cookies = [], array $query = []): AssertHttpResponse
    {
        $request = $this->call('GET', $uri, [], $headers, $cookies, $query);

        return $this->makeHandle($request);
    }

    /**
     * @param string[] $headers
     * @param string[] $cookies
     * @param mixed[] $body
     * @param string[][] $query
     */
    public function post(
        string $uri,
        array $body = [],
        array $headers = [],
        array $cookies = [],
        array $query = []
    ): AssertHttpResponse {
        $request = $this->call('POST', $uri, $body, $headers, $cookies, $query);

        return $this->makeHandle($request);
    }

    /**
     * @param string[] $headers
     * @param array<string, array<int, string>|string> $body
     * @param string[] $cookies
     * @param string[][] $query
     */
    public function put(
        string $uri,
        array $body,
        array $headers = [],
        array $cookies = [],
        array $query = []
    ): AssertHttpResponse {
        $request = $this->call('PUT', $uri, $body, $headers, $cookies, $query);

        return $this->makeHandle($request);
    }

    /**
     * @param string[] $headers
     * @param string[] $cookies
     */
    public function delete(string $uri, array $headers = [], array $cookies = []): AssertHttpResponse
    {
        $request = $this->call('DELETE', $uri, [], $headers, $cookies);

        return $this->makeHandle($request);
    }

    /**
     * @param string[] $headers
     * @param string[] $cookies
     */
    public function options(string $uri, array $headers = [], array $cookies = []): AssertHttpResponse
    {
        $request = $this->call('OPTIONS', $uri, [], $headers, $cookies);

        return $this->makeHandle($request);
    }

    protected function makeHandle(ServerRequestInterface $request): AssertHttpResponse
    {
        /** @var App $app */
        $app = static::getApp();
        /** @var Container $container */
        $container = $app->getContainer();
        $container->set(ServerRequestInterface::class, $request);

        /** @var ResponseInterface $response */
        $response = $app->handle($request);

        return AssertHttpResponse::create($response);
    }

    /**
     * @param string[] $headers
     * @param string[] $cookies
     * @param array<string, array<int, string>|string> $body
     * @param string[][] $queryParams
     */
    protected function call(
        string $method,
        string $uri,
        array $body = [],
        array $headers = [],
        array $cookies = [],
        array $queryParams = []
    ): ServerRequestInterface {

        /** @var App $app */
        $app = static::getApp();

        /** @var Container $container */
        $container = $app->getContainer();

        /** @var ServerRequestFactoryInterface $request */
        $request = $container->get(ServerRequestFactoryInterface::class);

        $serverRequest = $request->createServerRequest($method, $uri);

        if (!empty($body)) {
            $serverRequest = $serverRequest->withParsedBody($body);
        }

        if (!empty($headers)) {
            foreach ($headers as $name => $value) {
                $serverRequest = $serverRequest->withAddedHeader($name, $value);
            }
        }

        if (!empty($cookies)) {
            foreach ($cookies as $value) {
                $serverRequest = $serverRequest->withAddedHeader('Set-Cookie', $value);
            }
        }

        if (!empty($queryParams)) {
            $serverRequest = $serverRequest->withQueryParams($queryParams);
        }

        return $serverRequest;
    }
}
