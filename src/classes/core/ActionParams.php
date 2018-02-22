<?php
namespace ellsif\WelCMS;


class ActionParams
{
    private $urlParams;

    private $getParams;

    private $postParams;

    public function __construct(Route $route)
    {
        $this->urlParams = $route->getParams();
        $this->getParams = $_GET;
        $this->postParams = $_POST;
    }

    public function get($key, $urlDecode = true)
    {
        $param = $this->getParams[$key] ?? $this->urlParams[$key] ?? null;
        if ($param && $urlDecode) {
            $param = rawurldecode($param);
        }
        return $param;
    }

    public function post(string $key = null)
    {
        return $key ? ($this->postParams[$key] ?? null) : $this->postParams;
    }

    public function getPost($key, $urlDecode = true)
    {
        if (($get = $this->get($key, $urlDecode)) !== null) {
            return $get;
        }
        return $this->post($key);
    }

    public function postGet($key, $urlDecode = true)
    {
        if (($post = $this->post($key)) !== null) {
            return $post;
        }
        return $this->get($key, $urlDecode);
    }
}