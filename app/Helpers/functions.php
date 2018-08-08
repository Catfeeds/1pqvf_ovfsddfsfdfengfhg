<?php

$periphery = [     //获得周边类型
    '0'=>'银行',
    '1'=>'餐厅',
    '2'=>'宾馆',
    '3'=>'娱乐',
    '4'=>'酒店',
    '5'=>'商场',
    '6'=>'超市',
    '7'=>'影院',
];


/*
 * 上传图片
 */
function uploadpic($filename, $filepath)
{
    //        1.首先检查文件是否存在
    if (Request::hasFile($filename)){
        //          2.获取文件
        $file = Request::file($filename);
        //          3.其次检查图片手否合法
        if ($file->isValid()){
            //                先得到文件后缀,然后将后缀转换成小写,然后看是否在否和图片的数组内
            if(in_array( strtolower($file->extension()),['jpeg','bmp','jpg','gif','gpeg','png'])){
                //          4.将文件取一个新的名字
                $name = '.'.$file->extension();
                $newName = 'img'.time().rand(100000, 999999).$name;
                //           5.移动文件,并修改名字
                if($file->move($filepath,$newName)){
                    return $filepath.'/'.$newName;   //返回一个地址
                }else{
                    return 4;
                }
            }else{
                return 3;
            }

        }else{
            return 2;
        }
    }else{
        return 1;
    }
}

/**
 *  批量删除图片
 * 传入：$fine_arr图片完整路径的一维数组
 * @param $fine_arr
 */
function delPics($fine_arr){
    foreach ($fine_arr as $value){
        if(file_exists($value)){
            @unlink($value);
        }
    }
}

/**
 * 验证日期是否连续
 * @param $arr
 * @return int
 */
function _mycheck($arr){
    for($i=1; $i<count($arr); $i++){
        $lastone = strtotime($arr[$i-1]);
        $thisone = strtotime($arr[$i]);
        if($thisone - $lastone != 3600*24){
            return 2;
        }
    }
}

/**
 * 排序
 * @param $arr
 * @param int $order
 * @return mixed
 */
function sorts($arr,$order=0){
    for($i=1;$i<count($arr);$i++)
    {
        for($k=0;$k<count($arr)-$i;$k++)
        {
            if( $order == 0 ){
                if($arr[$k]>$arr[$k+1])
                {
                    $tmp=$arr[$k+1];
                    $arr[$k+1]=$arr[$k];
                    $arr[$k]=$tmp;
                }
            }elseif ($order == 1){
                if($arr[$k]<$arr[$k+1])
                {
                    $tmp=$arr[$k+1];
                    $arr[$k+1]=$arr[$k];
                    $arr[$k]=$tmp;
                }
            }
        }
    }
    return $arr;
}

/**
 * 验证接口 验证是否断签,如果没断签,坚持天数+1.如果断签,清零后将今天的(如果今天满足则1)覆盖上去,
 * @param $array
 * @param $time
 * @return bool
 */
function check($array,$time){
    foreach ($array  as $key=>$val){
        $arr[] = $val;
    }
    //排序
    $arr = sorts($arr);
    //把今天加进去
    $c = count($arr);
    $arr[$c] = $time;
    $check = _mycheck($arr);
    if( $check == 2 ){
        //出现断签
        $falg = true;
    }else{
        $falg = false;
    }
    return $falg;
}

/**
 * 验证连续签到多少天
 * @param $arr
 * @return mixed
 */
function SignIn($arr){
    //先将所有日期进行转换
    foreach ($arr as $k=>$v){
        $data_list[] = strtotime($v);
    }
    $res[0] = 0;
    $position = 0;
    $count = count($data_list);
    for($i=0;$i<$count;$i++){
        $d1 = $data_list[$i];
        if( $i+1 == $count ){
            break;
        }
        $d2 = $data_list[$i+1];
        if( $d2 - $d1 == 86400 ){
            $res[$position] = $res[$position]+1;
        }else{
            $position = $position + 1;
            $res[$position] = 0;
        }
    }
    $res = sorts($res,1);
    return $res[0];
}

/**
 *  检查时间
 * @param $array
 * @param $start_at
 * @param $end_at
 * @return mixed
 */
function check_times($array,$start_at,$end_at){
    //找到所有不在开始时间之前的下标
    foreach ( $array as $key=>$val ){
        $time = strtotime($val);//时间
        //如果这个时间,不再活动开始时间之后
        if( $time < $start_at ){
            unset($array[$key]);
        }elseif ( $time > $end_at ){//如果这个时间,不再结束时间之前  12-1 12-3
            unset($array[$key]);
        }
    }
    return $array;
}

/**
 * 高德地图坐标转换->百度地图
 * @param $gd_lat
 * @param $gd_lng
 * @return mixed
 */
function bd_encrypt( $gd_lat, $gd_lng ){
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    //    $x_pi = 3.14159265358979323846 * 3000.0 / 180.0;
    $x = $gd_lng;
    $y = $gd_lat;
    $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
    $arr['lng'] = $z * cos($theta) + 0.0065;
    $arr['lat'] = $z * sin($theta) + 0.006;
    return $arr;
}

/**
 *  计算两个经纬度之间的距离,单位为米
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @return float|int
 */
function getDistance($lat1, $lng1, $lat2, $lng2)
{
    /**
     * 求两个已知经纬度之间的距离,单位为米
     *
     * @param lng1 $ ,lng2 经度
     * @param lat1 $ ,lat2 纬度
     * @return float 距离，单位米
     */
    // 将角度转为弧度
    $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
    $radLat2 = deg2rad($lat2);
    $radLng1 = deg2rad($lng1);
    $radLng2 = deg2rad($lng2);
    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;
    $calculatedDistance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
    return  $calculatedDistance;
}

function rad($d){
    return $d * pi()/ 180.0;
}

/**
 * 计算两个坐标的距离
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @return float|int
 */
function GetDistance2($lat1,$lng1,$lat2, $lng2){
    $EARTH_RADIUS = 6378137;
    $radLat1 = rad($lat1);
    $radLat2 = rad($lat2);
    $a = $radLat1 - $radLat2;
    $b = rad($lng1) - rad($lng2);
    $s = 2 *asin(sqrt(pow(sin($a/2),2) +
            cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
    $s = $s * $EARTH_RADIUS;
    $s = round($s * 10000) / 10000;
    return $s;
}

/**
 * 批量生成随机的经纬度
 */
function get_poi_address($lng,$lat,$num){
    $addr = [
        '0'=>'银行',
        '1'=>'餐厅',
        '2'=>'宾馆',
        '3'=>'娱乐',
        '4'=>'酒店',
        '5'=>'商场',
        '6'=>'超市',
    ];
    $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
    $page_size = 20;//返回的poi数量
    //获取poi
    $url = "http://api.map.baidu.com/place/v2/search?query=". $addr[rand(0,count($addr)-1)] ."&location={$lat},{$lng}&radius=2000&output=json&ak={$ak}&page_size={$page_size}";
    $poi2 = file_get_contents($url);
    $json_poi2 = json_decode($poi2,true);
    $arr = [];//纬度
    foreach ( $json_poi2['results'] as $key=>$val ){
        //将返回的poi,进行模糊
        $arr[$key]['address'] = rand_latitude($val['location']['lng'],$val['location']['lat'],3);
    }
    $as = 0;
    foreach ($arr as $k=>$v){
        foreach ($v as $key=>$val){
            foreach ($val as $i=>$h){
                $array[$as]['lng'] = $h['lng'];
                $array[$as]['lat'] = $h['lat'];
//                $array[$as]['area'] = $h['area'];
                $as++;
            }
        }
    }
    //如果生成后的数量少于要生成的数量
    if( count($array) < $num  ){
        //根据第一次的请求返回的poi,作为起点,再次将这些poi发送出去,请求poi回来
        $array = cr($num,$json_poi2,$as,$array);
    }
    //如果生成的数量大于要生成的数量,则将其截取
    if( count($array) > $num ){
        $a = count($array);
        for ($i=$a;$i>$num;$i--){//202>200 删除201  201>200 200
            unset($array[$i-1]);
        }
    }
    return $array;
}

/**
 * 递归生成poi
 * @param $num 要生成的数量
 * @param $json_poi2  百度api 返回的结果集 $json_poi2="http://api.map.baidu.com/place/v2/search?query=". $addr[rand(0,count($addr)-1)] ."&location={$lat},{$lng}&radius=2000&output=json&ak={$ak}&page_size={$page_size}";
 * @param $as 当前array的下标
 * @param $array 容器
 * @return mixed 二维数组
 */
function cr($num,$json_poi2,$as,$array){
    $addr = [
        '0'=>'银行',
        '1'=>'餐厅',
        '2'=>'宾馆',
        '3'=>'娱乐',
        '4'=>'酒店',
        '5'=>'商场',
        '6'=>'超市',
    ];
    $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
    $page_size = 20;//返回的poi数量
    $arr = [];//纬度
    foreach ($json_poi2['results'] as $key=>$val){
        $url = "http://api.map.baidu.com/place/v2/search?query=". $addr[rand(0,count($addr)-1)] ."&location={$val['location']['lat']},{$val['location']['lng']}&radius=2000&output=json&ak={$ak}&page_size={$page_size}";
        $poi = file_get_contents($url);
        $json_pois = json_decode($poi,true);
        foreach ( $json_pois['results'] as $k=>$v ){
            $arr[$key]['address'] = rand_latitude($val['location']['lng'],$val['location']['lat'],3);
        }
    }
    foreach ($arr as $k=>$v){
        foreach ($v as $key=>$val){
            foreach ($val as $i=>$h){
                $array[$as]['lng'] = $h['lng'];
                $array[$as]['lat'] = $h['lat'];
                $as++;
            }
        }
    }
    if( count($array) < $num ){
        $array = cr($num,$json_pois,$as,$array);
    }
    return $array;
}

/**
 * 根据传入的详细地址,获得单个随机经纬度或多个随机经纬度
 *  详细地址 如  深圳市南山区科苑西工业区23栋
 */
function Latitude_and_longitude($address,$n){
    $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
    $u = "http://api.map.baidu.com/geocoder/v2/?address={$address}&output=json&ak={$ak}";
    $address_data = file_get_contents($u);
    $json_data = json_decode($address_data,true);
    if( $json_data == null ){
        return 2;
    }elseif ($json_data['status'] == 1){
        return 4;
    }
    if( $n > 1 ){//如果生成多个随机经纬度——调用方法
        $arr = get_poi_address($json_data['result']['location']['lng'],$json_data['result']['location']['lat'],$n);
        return $arr;
    }
    //生成一个随机经纬度
    $arr = rand_latitude($json_data['result']['location']['lng'],$json_data['result']['location']['lat'],$n);
    return $arr;
}

/**
 * 根据传入的地区,获得单个随机经纬度或多个随机经纬度
 * @param $key 要生成的地区名字
 * @param $n    要生成的数量
 * @return int|mixed 二维数组
 */
function Obtain_a_single_warp($key,$n){
    //从配置文件中随机获取一个地名
    $arr = config('address.match.'.$key);
    if( $n > 1 ){
        //如果生成数量不止1,则根据每100张,调用一次配置文件
        //如320张,则调用3次,前两次为一百,第三次调用时为全部
        //先判断是否大于100
        if( $n >= 100 ){
            $x = floor( $n / 100 );//599 = 5 -1
            for ( $i=0;$i<$x;$i++ )//
            {
                //调用配置文件,生成经纬度,生成数量为100
                $add = $arr[rand(0,count($arr)-1)];
                $latitude[] = Latitude_and_longitude($add,100);
            }
            $num = $n - $x * 100;//剩下的余数,再调用一次配置文件,生成经纬度
            $add = $arr[rand(0,count($arr)-1)];
            if( $num > 0 ){
                //有余数
                $latitude[] = Latitude_and_longitude($add,$num);
            }
            //将数组整合并返回
            $arr2 = [];
            foreach ($latitude as $key=>$val){
                foreach ($val as $k=>$v ){
                    $arr2[] = $v;
                }
            }
            return $arr2;
        }else{
            //小于一百,则调用一次
            $add = $arr[rand(0,count($arr)-1)];
            $latitude = Latitude_and_longitude($add,$n);
            return $latitude;
        }
    }
    $add = $arr[rand(0,count($arr)-1)];
    //将随机获取的地名,请求得出一个周边随机的经纬度
    $latitude =  Latitude_and_longitude($add,$n);
    return $latitude;
}

/**
 * 调整生成随机经纬度的值及字符串长度
 * @param $lng 经纬度
 * @param $lat
 * @param $n   要生成的数量
 * @return mixed 二位数组
 */
function rand_latitude($lng,$lat,$n){

    $array = array(0,1);
    $i = rand(0,1);
    for ( $k=0;$k<$n;$k++ ){
        if( $array[$i] == 0 ){
            //如果是0,则增
            $lng_max = $lng + 0.02;
            $lat_max = $lat + 0.01;
            $rl = mt_rand() / mt_getrandmax();
            $arr[$k]['lng'] = upd_10bit_str($lng + ($rl * ($lng_max - $lng)));
            $arr[$k]['lat'] = upd_10bit_str($lat + ($rl * ($lat_max - $lat)));
            return $arr;
        }
        //否则,则减
        $lng_min = $lng - 0.02;
        $lat_min = $lat - 0.01;
        $rl = mt_rand() / mt_getrandmax();
        $arr[$k]['lng'] = upd_10bit_str($lng + ($rl * ($lng - $lng_min)));
        $arr[$k]['lat'] = upd_10bit_str($lat + ($rl * ($lat - $lat_min)));
    }
    return $arr;
}

/**
 * 调整经纬度长度
 */
function adjust ( $arr ){
    $lng = mb_strlen($arr['lng']);
    $lat = mb_strlen($arr['lat']);
    //判断长度如果不足10位,则补零
    if( $lng < 10 ||  $lat < 10){
        $arr['lng'] = str_pad($arr['lng'],10,0);
        $arr['lat'] = str_pad($arr['lat'],10,0);
        return $arr;
    }
    //判断他们的长度,
    $arr['lng'] = substr($arr['lng'],0,10);
    $arr['lat'] = substr($arr['lat'],0,10);
    return $arr;
}

/**
 * 调整字符串长度
 * @param $string
 * @return bool|string
 */
function upd_10bit_str( $string ){
    $long = mb_strlen($string);
    //判断长度如果不足10位,则补零
    if( $long < 10 ){
        $string = str_pad($string,10,0);
        return  $string;
    }
    //截取他们的长度,
    $string = substr($string,0,10);
    return $string;
}




/**
 *  返回json
 * @param null $data 要返回的数据,如果是成功,可以只传入data
 * @param string $status  状态,默认为成功
 * @param string $msg 提示信息,默认为成功
 * @param int $code  code代码,默认为成功
 * @return string
 */
function res($data=null,$msg='查询成功',$status='success',$code=200){
    $data = [
        'status'=>$status,'msg'=>$msg,'code'=>$code, 'data'=>$data
    ];
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );exit;
}

/**
 * 比对时间是多久之前
 * @param $time 要比对的时间 2018-1-11 10:06:37
 * @return string
 */
function format_date($time){
    $time = strtotime( $time );
    $t=time()-$time;
    $f=array(
        '31536000'=>'年',
        '2592000'=>'个月',
        '604800'=>'星期',
        '86400'=>'天',
        '3600'=>'小时',
        '60'=>'分钟',
        '1'=>'秒'
    );
    foreach ($f as $k=>$v)    {
        if (0 !=$c=floor($t/(int)$k)) {
            return $c.$v.'前';
        }
    }
}
/**
 * 删除一个指定值的数组元素$value,并重新按0开始连续排序索引
 * $arr 数组 $value指定值，非数组
 * @param $arr
 * @return array
 */
function delByValue($arr, $value){
    if(!is_array($arr)){
        return $arr;
    }
    foreach($arr as $k=>$v){
        if($v == $value){
            unset($arr[$k]);
        }
    }
    $arr = array_values($arr);
    return $arr;
}

/**
 * 在数据库中获取的数据 Collection 转数组
 * 就是这么吊！
 * @param $obj
 * @return mixed
 */
function obj_arr ($obj){
    return json_decode(json_encode($obj),true);
}

/**
 * 新count()由于7.2不向下兼容，避免报错
 * @param $array_or_countable
 * @param int $mode
 * @return int
 */
function new_count($array_or_countable,$mode = COUNT_NORMAL){
    if(is_array($array_or_countable) || is_object($array_or_countable)){
        return count($array_or_countable, $mode);
    }else{
        return 0;
    }
}

/**
 * 数组每个元素的值减1
 * 传入数组$arr 如果不为整数就清除掉 如果减1等于0就清除掉
 * @param $arr
 * @return mixed
 */
function arr_sub_1($arr){
    foreach ($arr as $k => $v){
        if(is_int($v)){
            if(($v - 1) == 0){
                unset($arr[$k]);
            }else{
                $arr[$k] = $v - 1;
            }
        }else{
            unset($arr[$k]);
        }
    }
    return $arr;
}

/**
 * 删除指定值且只删除一次
 * arr被删除的数组不能为null coll要删除值的集合不能为null
 * @param $arr
 * @param $coll
 * @return array|string
 */
function arr_dlt_val_once($arr,$coll){
    if(is_array($arr) && is_array($coll)){
        foreach ($coll as $v){
            unset(
                $arr[array_search($v ,$arr)]
            );
        }
        return $arr;
    }
    return '传入参数不正确';
}

/**
 * 强制下载某文件
 * @file - path to file文件路径
 */
function force_download($file){
    if ((isset($file))&&(file_exists($file))) {
        header("Content-length: ".filesize($file));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        readfile("$file");
    }else{
        echo "No file selected";
    }
}

/**
 * 返回固定长度的经纬度
 * $type 1：一个地址生成多个 其他：多个地址生成多个
 * @param $type
 * @param $address
 * @param $n
 * @return int|mixed
 */
function get_latitude($type, $address, $n){
    set_time_limit(0);//设置超时时间
    if ($type == 1) {
        $latitude = Latitude_and_longitude($address, $n);
        switch ($latitude) {
            case 2:
                return 2;
                break;
            case 3:
                return 3;
                break;
            case 4:
                return 4;
                break;
        }
    } else {
        $latitude = Obtain_a_single_warp($address, $n);
    }
    return $latitude;
}



/**
 * 返回经纬度的二维数组
 * $type 1：一个地址生成多个 其他：多个地址生成多个
 * @param $type
 * @param $address
 * @param $n
 * @return int|mixed
 */
function get_lat_lng($type, $address, $n){
    set_time_limit(0);//设置超时时间
    if ($type == 1) {
        $latitude = Latitude_and_longitude($address, $n);
        $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
        $u = "http://api.map.baidu.com/geocoder/v2/?address={$address}&output=json&ak={$ak}";
        $address_data = file_get_contents($u);
        $json_data = json_decode($address_data,true);
        if( $json_data == null ){
            return 2;
        }elseif ($json_data['status'] == 1){
            return 4;
        }
        if( $n > 1 ){
            //调用其他方式生成
            $arr = get_poi_address($json_data['result']['location']['lng'],$json_data['result']['location']['lat'],$n);
            return $arr;
        }
        $arr = rand_latitude($json_data['result']['location']['lng'],$json_data['result']['location']['lat'],$n);
        return $arr;
        switch ($latitude) {
            case 2:
                return 2;
                break;
            case 3:
                return 3;
                break;
            case 4:
                return 4;
                break;
        }
    } else {
        $latitude = Obtain_a_single_warp($address, $n);
    }
    return $latitude;
}

/**
 * 判断一个值是否在指定范围内（两边包含等于）
 * @param $min
 * @param $integer
 * @param $max
 * @return bool
 */
function is_in_range($min, $integer, $max){
    if(is_numeric($min) && is_numeric($integer) && is_numeric($max)){
        if($min<=$integer && $integer<=$max){
            return true;
        }else{
            return false;
        }
    }
    return false;
}

/**
 * （按照config('address.match')）适用大于300条数据! 生成平均分布的经纬度并打乱经纬度注意：深圳范围内
 * $n每个点要生成的数量
 * @param $n
 * @return mixed 二维数组 总生成数=350*$n
 */
function make_sz_long_lat($n){
    if($n>0){
        set_time_limit(0);//设置超时时间
        //从配置文件获取所有地名
        $all_adr = config('address.match');//所有地区 二维数组，并记录按哪个区生成的
        $result = array_reduce($all_adr, function ($result, $value) {
            return array_merge($result, array_values($value));
        }, array());//转一维数组
        $arr = [];
        foreach ($result as $k => $v){
            $arr[] =  Latitude_and_longitude($v,$n);
        }
        //转一维数组
        $all_arr = array_reduce($arr, function ($all_arr, $value) {
            return array_merge($all_arr, array_values($value));
        }, array());//转二维数组
        shuffle($all_arr);//打乱
        return  $all_arr;
    }else{
        return false;
    }
}
/**
 * 生成$n个优惠券唯一id（10亿级）
 * 换个名字即可换作另一类编码
 * @param $n
 * @return array|int
 */
function create_unique_max_8bit_int($n){
    if($n>0){
        $arr_uuid = [];
        for($i=0;$i<$n;++$i){
            $number = mt_rand(1,999999999);
            $arr_unq = empty(Redis::get('unique_9bit_int')) ? [] : unserialize(Redis::get('unique_9bit_int')) ;//获取编码池或初始化为数组
            if ( in_array($number,$arr_unq)) {//如果已经存在
                create_unique_max_8bit_int();//重新生成
            } else {//如果不存在，系列化并存储
                array_push($arr_unq,$number);
                $serialize_arr = serialize($arr_unq);
                Redis::set('unique_9bit_int', $serialize_arr);
                $arr_uuid[$i] = $number;
            }
        }
        return $arr_uuid;
    }else{
        return ['status' => 'fail', 'msg' => '添加失败'];
    }
}

/**
 * 根据经纬度获取国家、省份、城市及周边数据
 * @param $ak
 * @param $longitude
 * @param $latitude
 * @return array|mixed
 */
function get_address_component($ak, $longitude, $latitude){
    $url = "http://api.map.baidu.com/geocoder/v2/?location={$latitude},{$longitude}&output=json&pois=0&ak={$ak}";
    $addr = file_get_contents($url);
    $addr = json_decode($addr, true);
    return $addr;
}


/**
 * 为数组下的每个元素添加前缀
 * @param $string_callback_name
 * @param $arr
 * @return array
 */
function add_arr_prefix($string_callback_name,$arr)
{
    $arr = array_map($string_callback_name,$arr);
    return $arr;
}
/**
 * 添加url 图片前缀
 * @param $v
 * @return string
 * 遍历二维数组下的每个需要修改为到现在为止多久的元素，并替换
 */
function url_prefix($v)
{
    return(config('app.url').$v);
}

/**
 * 计算二维数组下的某个元素的值转化为多久之前
 * @param $arr_2d
 * @param $key
 * @return mixed
 */
function how_long_2d_arr($arr_2d,$key){
    foreach ($arr_2d as $k => $v){
        $arr_2d[$k][$key] = format_date($v[$key]);
    }
    return $arr_2d;
}