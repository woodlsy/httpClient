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
     * @var string 证书路径
     */
    protected $sslCert = '';

    /**
     * @var string 秘钥路径
     */
    protected $sslKey = '';

    /**
     * @var bool 是否压缩
     */
    protected $isZip = false;

    protected $result = null;

    /**
     * @var bool 保持数据data原有格式
     */
    protected $keepDataFormat = false;

    /**
     * @var bool cookies
     */
    protected $cookies = false;
    protected $cookiesFilePath = 'cookies.txt';

    /**
     * @var bool 开启重定向跳转
     */
    protected $redirect = true;

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
        $this->isHttps = preg_match('/^https?:/i', $url) > 0;
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
     * 设置证书
     *
     * @author woodlsy
     * @param string $cert
     * @param string $key
     * @return $this
     */
    public function setSSLCert(string $cert, string $key)
    {
        $this->sslCert = $cert;
        $this->sslKey  = $key;
        return $this;
    }

    /**
     * header是否压缩
     *
     * @author woodlsy
     * @return $this
     */
    public function isZip()
    {
        $this->isZip = true;
        return $this;
    }

    /**
     * 获取结果
     *
     * @author yls
     * @return null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * 设置数据格式是否保留
     *
     * @author yls
     * @param bool $keep
     * @return $this
     */
    public function setKeepDataFormat(bool $keep)
    {
        $this->keepDataFormat = $keep;
        return $this;
    }

    /**
     * 开启cookies
     *
     * @author yls
     * @param string|null $cookiesFilePath
     * @return $this
     */
    public function openCookies(string $cookiesFilePath = null)
    {
        if (!empty($cookiesFilePath)) {
            $this->cookiesFilePath = $cookiesFilePath;
        }
        $this->cookies = true;
        return $this;
    }

    /**
     * 重定向自动跳转
     *
     * @author yls
     * @param bool $redirect
     * @return $this
     */
    public function redirect(bool $redirect)
    {
        $this->redirect = $redirect;
        return $this;
    }

    /**
     * 随机IP，只是简单的伪造，最好还是通过代理
     *
     * @author yls
     * @return $this
     */
    public function randIp()
    {
        $ipLong         = array(
            array('607649792', '608174079'), //36.56.0.0-36.63.255.255
            array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
            array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
            array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
            array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
            array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
            array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
            array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
            array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
            array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
        );
        $rand_key       = mt_rand(0, 9);
        $ip             = long2ip(mt_rand($ipLong[$rand_key][0], $ipLong[$rand_key][1]));
        $this->header[] = 'CLIENT-IP:' . $ip;
        $this->header[] = 'X-FORWARDED-FOR:' . $ip;
        $this->header[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36';
        $this->header[] = 'Content-Type: application/json; charset=UTF-8';
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

        if (!empty($this->sslCert)) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $this->sslCert);
        }

        if (!empty($this->sslKey)) {
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $this->sslKey);
        }

        if ($this->isZip) {
            curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, "gzip,deflate");
        }

        if ($type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($this->data) && !$this->keepDataFormat ? http_build_query($this->data) : $this->data);
        }

        if (!empty($this->header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        }

        if ($this->cookies) {
            if (!is_file($this->cookiesFilePath)) {
                file_put_contents($this->cookiesFilePath, '');
            }
            $this->cookiesFilePath = realpath($this->cookiesFilePath);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiesFilePath);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiesFilePath);
        }

        if ($this->redirect) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        if (false === $result) {
            throw new HttpClientException(curl_error($ch));
        }
        $this->result = $result;
        $info         = curl_getinfo($ch);
        if (200 !== (int) $info['http_code']) {
            return 'curl status:' . $info['http_code'] . ' curl url:' . $this->url;
        }
        return $result;
    }

}