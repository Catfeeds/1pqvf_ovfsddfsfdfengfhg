<?php
namespace App\Libs;
/**
 * 根据地理坐标获取国家、省份、城市，及周边数据类(利用百度 API实现)
 * 百度密钥获取方法：http://lbsyun.baidu.com/apiconsole/key?application=key（需要先注册百度开发者账号）
 * Func:
 * Public  getAddressComponent 根据地址获取国家、省份、城市及周边数据
 * Private toCurl              使用curl调用百度Geocoding API
 */
/**
 * 确保正确安装cURL扩展
 */
if (!function_exists('curl_init'))
{
    throw new Exception('OpenAPI needs the cURL PHP extension.');
}

class baidu_map
{

// 百度相关变量
    const GEO_URL = 'http://api.map.baidu.com/geocoder/v2/';//geocoder编码服务
    const PLACE_URL = 'http://api.map.baidu.com/place/v2/';//place服务

    /**
     * 根据经纬度获取国家、省份、城市及周边数据
     * @return Array
     * @param $ak                   百度ak(密钥)
     * @param $longitude            经度
     * @param $latitude             纬度
     * @param $pois                 是否显示周边数据 0不显示周边数据;1显示周边数据
     * @return array|mixed
     */
    public static function getAddressComponent($ak, $longitude, $latitude, $pois){
        $param = array(
            'ak' => $ak,
            'location' => implode(',', array($latitude, $longitude)),
            'pois' => $pois,
            'output' => 'json'
        );

        // 请求百度api
        $response = self::toCurl(self::GEO_URL, $param);

        $result = array();

        if($response){
            $result = json_decode($response, true);
        }

        return $result;

    }

    /**
     * Curl—————调用百度Geocoding API的 curl
     * @param  String $url    请求的地址
     * @param  Array  $param  请求的参数
     * @return JSON
     */
    private static function toCurl($url, $param=array()){

        $ch = curl_init();

        if(substr($url,0,5)=='https'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));

        $response = curl_exec($ch);

        if($error=curl_error($ch)){
            return false;
        }

        curl_close($ch);

        return $response;

    }
}


?>