<?php

/**
 *+----------------------------------------------------------
 * 调用接口(使用自定义函数http_curl)
 *+----------------------------------------------------------
 * @static
 * @access public
 *+----------------------------------------------------------
 * @param string $data 请求所需要的参数数组
 * @param string $type 请求的类型(get/post) 默认是get请求
 *+----------------------------------------------------------
 * @return json
 *+----------------------------------------------------------
 */
function get_url_data($data=array(), $type='get')
{
    $info = array(
        'm' => request() -> module(),
        'c' => request() -> controller(),
        'a' => request() -> action()
    );

    if(empty($data['url'])){
        $url = 'http://api.com/index.php/';
        $url .= (!empty($data['m']) ? : $info['m']) . '/';
        $url .= (!empty($data['c']) ? : $info['c']) . '/';
        $url .= !empty($data['a']) ? : $info['a'];
    }else{
        $url = $data['url'];
    }
    // dump($url);die;
    unset($data['url']);

    $res = json_decode(http_curl($url, $data, $type), true);
    return $res;
}
/**
 *+----------------------------------------------------------
 * http请求(使用php内置的curl函数)
 *+----------------------------------------------------------
 * @static
 * @access public
 *+----------------------------------------------------------
 * @param string $url 需要请求的url
 * @param string $data 请求所需要的参数数组
 * @param string $type 请求的类型(get/post) 默认是get请求
 *+----------------------------------------------------------
 * @return string|bool
 *+----------------------------------------------------------
 */
function http_curl($url, $data=array(), $type='get')
{
    if(!function_exists('curl_init')){
        echo 'curl扩展没有打开！'; exit;
    }
    // 1.开启会话
    $ch = curl_init();
    // 2.设置会话配置
    if($type == 'post'){
        // 配置为post请求
        curl_setopt($ch, CURLOPT_POST, true);
        // 数据按post数据进行请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }else{//组装get请求
        $str = '';
        foreach ($data as $key => $value) {
            $str .= '/'.$key.'/'.$value;
        }
        $url = trim($url, '/');
        $url .= $str;
    }
    // 设置具体的url请求
    curl_setopt($ch, CURLOPT_URL, $url);
    // 设置返回的数据不是直接输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 3.执行一个会话,发送http请求
    $res = curl_exec($ch);
    // 4.结束会话
    curl_close($ch);
    return $res;
}


/**
 *+----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 *+----------------------------------------------------------
 * @static
 * @access public
 *+----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符/是否显示省略号
 *+----------------------------------------------------------
 * @return string
 *+----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
    if(function_exists("mb_substr")){
        if($suffix){
            if(strlen($str)>$length)
                return mb_substr($str, $start, $length, $charset)."...";
            else
                return mb_substr($str, $start, $length, $charset);
        }else{
            return mb_substr($str, $start, $length, $charset);
        }
    }elseif(function_exists('iconv_substr')) {
        if($suffix){
            return iconv_substr($str,$start,$length,$charset);
        }else{
            return iconv_substr($str,$start,$length,$charset);
        }
    }
}