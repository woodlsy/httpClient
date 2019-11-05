<?php

namespace woodlsy\httpClient;

class HttpCurl
{
    /**
     * @var string 请求地址
     */
    protected $url = null;

    /**
     * @var array 请求参数
     */
    protected $data = null;

    /**
     * @var bool 是否是https
     */
    protected $isHttps = false;

    protected $header = [];

    /**
     * 设置请求链接
     *
     * @author woodlsy
     * @param string $url
     * @return HttpCurl
     */
    public function setUrl(string $url) : HttpCurl
    {
        $this->url     = $url;
        $this->isHttps = preg_match('/^https?:/i', $url) > 0 ? true : false;
        return $this;
    }

    /**
     * 设置参数
     *
     * @author woodlsy
     * @param array|string $data
     * @return HttpCurl
     */
    public function setData($data) : HttpCurl
    {
        $this->data = $data;
        return $this;
    }

    /**
     * get curl
     *
     * @author woodlsy
     * @return string
     * @throws HttpClientException
     */
    public function get()
    {
        return $this->fetch('GET');
    }

    /**
     * post curl
     *
     * @author woodlsy
     * @return string
     * @throws HttpClientException
     */
    public function post()
    {
        return $this->fetch('POST');
    }

    /**
     * 设置header
     *
     * @author yls
     * @param $str
     * @return $this
     */
    public function setHeader($str)
    {
        $this->header[] = $str;
        return $this;
    }

    /**
     * curl
     *
     * @author woodlsy
     * @param $type
     * @return string
     * @throws HttpClientException
     */
    private function fetch($type) : string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (true === $this->isHttps) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        if ($type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($this->data) ? http_build_query($this->data) : $this->data);
        }

        if (!empty($this->header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        if (false === $result) {
            throw new HttpClientException(curl_error($ch));
        }
        $info = curl_getinfo($ch);
        if (301 === (int) $info['http_code']) {
            return $this->setUrl($info['redirect_url'])->fetch($type);
        }elseif (200 !== (int)$info['http_code']) {
            throw new HttpClientException('curl status:' . $info['http_code']);
        }
        return $result;
    }

}