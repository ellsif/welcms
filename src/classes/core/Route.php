<?php

namespace ellsif\WelCMS;


class Route
{
    private $requestUri;

    private $scheme;

    private $host;

    private $user;

    private $pass;

    private $fragment;

    private $paths;

    private $params;

    private $requestMethod;

    private $requestPath;

    private $service;

    private $action;

    private $auth;

    private $type;

    public function __construct($requestUri)
    {
        $this->requestUri = $requestUri;
        $urlInfo = RoutingUtil::parseUrl($requestUri);
        $this->scheme = $urlInfo['scheme'] ?? '';
        $this->host = $urlInfo['host'] ?? '';
        $this->port = $urlInfo['port'] ?? '';
        $this->user = $urlInfo['user'] ?? '';
        $this->pass = $urlInfo['pass'] ?? '';
        $this->fragment = $urlInfo['fragment'] ?? '';
        $this->paths = $urlInfo['paths'] ?? [];
        $this->params = $urlInfo['params'] ?? [];
        $this->requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->requestPath = implode('/', $urlInfo['paths']) . '/';

        $this->action = 'index';
        $this->type = 'html';
        $this->paths = [];
        $this->params = [];
    }

    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPass(): string
    {
        return $this->pass;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    public function getRequestPath(): string
    {
        return $this->requestPath;
    }

    /**
     * サービスクラスの完全修飾名をSETします。
     */
    public function setService(string $service)
    {
        $this->service = $service;
    }

    /**
     * サービスクラスの完全修飾名をGETします。
     */
    public function getService(): ?string
    {
        return $this->service;
    }

    /**
     * アクションメソッド名をSETします。
     */
    public function setAction(string $action)
    {
        $this->action = $action;
    }

    /**
     * アクションメソッド名を取得します。
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAuth(string $auth)
    {
        $this->auth = $auth;
    }

    public function getAuth(): ?string
    {
        return $this->auth;
    }

    public function setType(string $type)
    {
        $this->type = $type ? $type : 'html';
    }

    public function getType(): string
    {
        return $this->type;
    }
}