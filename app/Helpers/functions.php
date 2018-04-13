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
     * 验证日期是否连续
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
     *  排序
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
     *  验证接口 验证是否断签,如果没断签,坚持天数+1.如果断签,清零后将今天的(如果今天满足则1)覆盖上去,
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

     /*
      * 验证连续签到多少天
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

    /*
     * 检查时间
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

    /*
     * 高德地图坐标转换->百度地图
     */
    //22.5443700000,113.9446000000 //高德
    //22.5500115538,113.9511168227 百度
    //22.550035199525 113.95115135714---
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

    /*
     * 计算两个经纬度之间的距离,单位为米
     */
    function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6367000; //approximate radius of earth in meters
        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;

        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;

        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        //单位为米
        return round($calculatedDistance);
    }

    function rad($d){
        return $d * pi()/ 180.0;
    }

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

    /*
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
     * @param $address 详细地址 如  深圳市南山区科苑西工业区23栋
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
        if( $n > 1 ){
            //调用其他方式生成
            $arr = get_poi_address($json_data['result']['location']['lng'],$json_data['result']['location']['lat'],$n);
            return $arr;
        }
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
     * 生成随机经纬
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
                $arr[$k]['lng'] =  ($lng + ($rl * ($lng_max - $lng)));
                $arr[$k]['lat'] =  ($lat + ($rl * ($lat_max - $lat)));
                return $arr;
            }
            //否则,则减
            $lng_min = $lng - 0.02;
            $lat_min = $lat - 0.01;
            $rl = mt_rand() / mt_getrandmax();
            $arr[$k]['lng'] =  ($lng + ($rl * ($lng - $lng_min)));
            $arr[$k]['lat'] =  ($lat + ($rl * ($lat - $lat_min)));
        }
        return $arr;
    }

    /*
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
     *  返回json
     * @param null $data 要返回的数据,如果是成功,可以只传入data
     * @param string $status  状态,默认为成功
     * @param string $msg 提示信息,默认为成功
     * @param int $code  code代码,默认为成功
     * @return string
     */
    function res($data=null,$msg='查询成功',$status='success',$code=200){
        $data = [
            'status'=>$status,'msg'=>$msg,'code'=>$code, 'data'=>  $data
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
?>