<?php
namespace woodlsy\httpClient;

class HttpCurl
{
    /**
     * @var string 请求地址
     */
    public $url = null;

    /**
     * @var array 请求参数
     */
    public $data = null;

    /**
     * @var bool 是否是https
     */
    public $isHttps = false;

    /**
     * 设置请求链接
     *
     * @author woodlsy
     * @param string $url
     * @return HttpCurl
     */
    public function setUrl(string $url) : HttpCurl
    {
        $this->url = $url;
        $this->isHttps = preg_match('/^https?:/i', $url) > 0 ? true : false;
        return $this;
    }

    /**
     * 设置参数
     *
     * @author woodlsy
     * @param array $data
     * @return HttpCurl
     */
    public function setData(array $data) : HttpCurl
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

        if(true === $this->isHttps){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        if($type == 'POST'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        if(false === $result){
            throw new HttpClientException(curl_error($ch));
        }
        $info = curl_getinfo($ch);
        if($info['http_code'] != '200'){
            throw new HttpClientException('curl status:'.$info['http_code']);
        }
        return $result;
    }

}