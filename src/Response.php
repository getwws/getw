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

    /**
     * 创建Response
     * @param null $content 响应内容
     * @param int $statusCode  HTTP Code
     * @param null $headers Headers
     * @return Response
     */
    public static function create($content = null, $statusCode = 200, $headers = null) {
        return new Response($content, $statusCode, $headers);
    }

    /**
     * 创建Json Response
     * @param null|array|object $content 响应内容
     * @param int $statusCode  HTTP Code
     * @param null $headers Headers
     * @return Response
     */
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

    /**
     * 设置Header
     * @param string $header Name
     * @param string $value  Value
     * @return $this
     */
    public function setHeader($header, $value) {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * 设置响应内容
     * @param string $content 响应内容
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * Response发送至浏览器
     * @throws \Exception
     */
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

    /**
     * Redirect
     * @param string $url
     * @throws \Exception
     */
    public function redirect($url) {
        if (\headers_sent()) {
            throw new \Exception('tried to change http response code after sending headers!');
        }
        $this->setHeader('Location', $url);
        die;
    }

}
