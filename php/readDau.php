<?php

ini_set('memory_limit','256M');

class readDau {

    const INSERT = "INSERT INTO `stat_daily20170622` ( `access_token`, `user_id`, `user_key`, `user_name`, `store_id`, `client_os`, `client_os_version`, `client_type`, `client_name`, `client_version`, `client_device_id`, `client_ip`, `operate_behavior`,  `dataset`, `create_time`)
VALUES \n";

    public function __construct()
    {

    }

    private function setSqlValue($dau)
    {
        $value = "(";
        foreach ($dau as $k => $v){
            if (is_string($v)) {
                $value .= "'".$v."',";
            }else {
                $value .= $v.",";
            }
        }

        $value = substr($value, 0, -1).")";
        return $value;
    }

    private function stringToArray($string)
    {
        $string = str_replace("{", "", $string);
        $string = str_replace("}", "", $string);
        $row = explode(',', $string);
        $result = array();
        foreach ($row as $v)
        {
            $field = explode(':', $v);
            $result[trim($field[0])] = empty(trim($field[1])) ? '': trim($field[1]);
        }
        return $result;
    }

    public function setSqlParam($params)
    {
        $dau['access_token'] = empty($params['access_token'])?'':$params['access_token'];
        $dau['user_id'] = empty($params['user_id'])?0:intval($params['user_id']);
        $dau['user_key'] = empty($params['user_key'])?'':$params['user_key'];
        $dau['user_name'] = empty($params['user_name'])?'':$params['user_name'];
        $dau['store_id'] = empty($params['store_id'])?0:intval($params['store_id']);
        $dau['client_os'] = empty($params['p10'])?'':$params['p10'];
        $dau['client_os_version'] = empty($params['p7'])?'':$params['p7'];
        $dau['client_type'] = empty($params['p9'])?'':$params['p9'];
        $dau['client_name'] = $params['client_name'];
        $dau['client_version'] = empty($params['p1'])?'':$params['p1'];
        $dau['client_device_id'] = empty($params['device_id'])?'':$params['device_id'];
        $dau['client_ip'] = $params['client_ip'];
        $dau['operate_behavior'] = empty($params['operate_behavior'])?'':$params['operate_behavior'];
        $dau['dataset'] = $params['dataset'];
        $dau['create_time'] = $params['create_time'];

        return $dau;
    }

    public function readBossAccess()
    {
        $insert = self::INSERT;
        $file_path = "/Users/zhangzhidong/api.boss.bqmart.cn_22.log";
        if(file_exists($file_path)){
            $file_arr = file($file_path);
            for($i=0;$i<count($file_arr);$i++){//逐行读取文件内容
                $row = explode('|', $file_arr[$i]);

                $params = empty($row[2]) ? array() : $this->stringToArray($row[2]);

                $params['client_name'] = 'bqboss';
                $params['client_ip'] = $row[1];
                $params['dataset'] = $row[1];
                $params['create_time'] = strtotime($row[0]);


                $dau = $this->setSqlParam($params);

                $insert .= $this->setSqlValue($dau).','.PHP_EOL;
            }
            $insert = substr($insert, 0, -2).";";
            file_put_contents('/Users/zhangzhidong/readBOSSDau.sql', $insert);
        }
    }

    /**
     * url的字符串转化为数组
     *
     * @param $query
     * @return array
     */
    private function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    public function readApiAccess()
    {
        $insert = self::INSERT;
        $file_path = "/Users/zhangzhidong/api.access.log_22.log";
        if(file_exists($file_path)){
            $file_arr = file($file_path);
            $index = 1;
            for($i=0;$i<count($file_arr);$i++){//逐行读取文件内容
                $row = explode('|', $file_arr[$i]);

                if ($row[1]=="140.205.225.189") {
                    continue;
                }

                $params = array();
                $method = explode(" ", $row[2]);
                if (strpos($method[1],'.php')
                    || strpos($method[1],'.html')
                    || strpos($method[1],'.action')
                    || strpos($method[1],'/.')
                    || strpos($method[1],'/_')
                    || strpos($method[1],'.asp')
                    || strpos($method[1],'/CVS')
                    || strpos($method[1],'.jsp')
                    || strpos($method[1],'.xml')
                    || strpos($method[1],'.xtp')
                    || strpos($method[1],'test')
                    || strpos($method[1],'.ini')
                    || strpos($method[1],'document')
                    || $method[1]=='/') {
                    continue;
                }
                if ($method[0] == 'GET') {

                    $request = explode('?', $method[1]);
                    $operate_behavior = $request[0];

                    if (count($request)>1) {
                        $params = $this->convertUrlQuery($request[1]);
                    }
                }elseif($method[0] == 'POST') {
                    $request = explode('?', $method[1]);
                    $operate_behavior = $request[0];
                    if (count($request)>1) {
                        $params = $this->convertUrlQuery($request[1]);
                    }
                }else{
                    continue;
                }

                if (count($row)>3 && trim($row[3])!='-' && trim($row[3])!='') {
                    $params = array_merge($params,$this->convertUrlQuery($row[3]));
                }

                $params['operate_behavior'] = $operate_behavior;
                $params['client_name'] = 'bqmart';
                $params['client_ip'] = $row[1];
                $params['dataset'] = $row[1];
                $params['create_time'] = strtotime($row[0]);

                $dau = $this->setSqlParam($params);
                $insert .= $this->setSqlValue($dau).','.PHP_EOL;

               // if (mb_strlen($insert,"UTF8")> 10*1024*1024) {
               //     $insert = substr($insert, 0, -2).";";
               //     file_put_contents('/Users/zhangzhidong/readAPIDau'.$index.'.sql', $insert);
               //     $insert =self::INSERT;
               //     $index ++;
               // }
            }
            $insert = substr($insert, 0, -2).";";
            file_put_contents('/Users/zhangzhidong/readAPIDau.sql', $insert);
        }
    }

    public function run()
    {
//        $this->readBossAccess();
        $this->readApiAccess();
    }
}

$readDau = new readDau();
$readDau->run();