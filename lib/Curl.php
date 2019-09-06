<?php namespace Lib;
/**
 * curl request tool
 */
class Curl
{
    const DECODER_JSON = 'jsonDecode';
    const DECODER_NONE = 'noneDecode';

    protected $try_times = 3;
    protected $timeout = 10;
    protected $decoder = self::DECODER_JSON;  // json_decode default
    protected $headers = array();


    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
    }

    public function setTryTimes($try_times)
    {
        $this->try_times = $try_times;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = (int)$timeout;
        return $this;
    }

    public function setDecoder($decoder)
    {
        $this->decoder = $decoder;
        return $this;
    }

    public function setHeaders($value)
    {
        $this->headers = $value;
        return $this;
    }

    /**
     * POST
     * @param mixed $params
     * @return mixed
     * @throws \Exception
     */
    public function post($url, $params)
    {
        $res = $this->request('POST', $url, $params);
        if ($res === false && $this->try_times > 1) {
            $this->try_times -= 1;
            $res = $this->post($url, $params);
        }
        return $res;
    }

    /**
     * GET
     * @param mixed $params
     * @return mixed
     * @throws \Exception
     */
    public function get($url, $params = null)
    {
        $res = $this->request('GET', $url, $params);
        if ($res === false && $this->try_times > 1) {
            $this->try_times -= 1;
            $res = $this->get($url, $params);
        }
        return $res;
    }

    /**
     * post json
     * @param mixed $params
     * @return mixed
     * @throws \Exception
     */
    public function postJson($url, $params)
    {
        $res = $this->request('POSTJSON', $url, $params);
        if ($res === false && $this->try_times > 1) {
            $this->try_times -= 1;
            $res = $this->postJson($url, $params);
        }
        return $res;
    }


    /**
     * 获取请求内容
     * @param $method
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    protected function request($method, $url, $params)
    {
        $headers = $this->headers;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildPostData($params));
        } elseif ($method == 'POSTJSON') {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildJsonData($params));
        } else {
            $url = $this->buildUrl($url, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);

        if ($res === false) {
            $errmsg = curl_error($ch);
            $errcode = curl_errno($ch);
            curl_close($ch);
            throw new \Exception($errmsg, $errcode);
        } else {
            $de_method = $this->decoder;
            $res = $this->$de_method($res);
        }
        curl_close($ch);
        return $res;
    }


    protected function buildUrl($url, $mixed_data = '')
    {
        $query_string = '';
        if (!empty($mixed_data)) {
            $query_mark = strpos($url, '?') > 0 ? '&' : '?';
            if (is_string($mixed_data)) {
                $query_string .= $query_mark . $mixed_data;
            } elseif (is_array($mixed_data)) {
                $query_string .= $query_mark . http_build_query($mixed_data, '', '&');
            }
        }
        return $url . $query_string;
    }

    protected function buildPostData($data)
    {
        if (is_string($data)) {
            return $data;
        } else {
            return http_build_query($data, '', '&');
        }
    }

    protected function buildJsonData($data)
    {
        if (is_string($data)) {
            return $data;
        } else {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
    }

    protected function noneDecode($data)
    {
        return $data;
    }

    protected function jsonDecode($data)
    {
        return json_decode($data, true);
    }
}