<?php

// +----------------------------------------------------------------------
// | H1CMS © OpenSource CMS
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.h1cms.com All rights reserved.
// | Copyright (c) 2014-2016 嘉兴领格信息技术有限公司，并保留所有权利。
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Allen <allen@lg4.cn>
// +----------------------------------------------------------------------

namespace getw;

/**
 * Class Response
 * @package getw
 */
class Response {

    private $statusCode = 200;
    private $headers = [];
    private $content;

    public function __construct($content = null, $statusCode = 200, $headers = []) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function create($content = null, $statusCode = 200, $headers = null) {
        return new Response($content, $statusCode, $headers);
    }

    public static function toJson($content = null, $statusCode = 200, $headers = null) {
        $json = json_encode($content);
        if ($json === false) {
            $json = json_encode(array("jsonError", json_last_error_msg()));
            if ($json === false) {
                $json = '{"jsonError": "unknown"}';
            }
            http_response_code(500);
        }
        return (new Response($content, $statusCode, $headers))->setHeader('Content-Type', 'application/json;charset=utf-8');
    }

    public function setStatusCode($code) {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader($header, $value) {
        $this->headers[$header] = $value;
        return $this;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function send() {
        if (\headers_sent()) {
            throw new \Exception('tried to change http response code after sending headers!');
        }
        http_response_code($this->statusCode);
        foreach ($this->headers as $header => $value) {
            header(strtoupper($header) . ': ' . $value);
        }
        echo $this->body;
    }

    public function redirect($url) {
        if (\headers_sent()) {
            throw new \Exception('tried to change http response code after sending headers!');
        }
        $this->setHeader('Location', $url);
        die;
    }

}
