<?php
/*
        php Cloud 系统集成 登陆并查看用户表记录
        by wanghl 2014-12-25
        change by wanghl 2014-12-16
        提高程序运行效率 代码中“ ->' 双引号改为单引号

        测试环境 Cloud5.0

        注意：需要先定义【动态表单服务列表】的业务对象“用户”的View 查看接口

        在php的运行环境中Copy如下代码即可运行成功
        php.ini 需要开放 extension=php_curl.dll

        php下载地址：
        http://windows.php.net/download/
        iis+php 下相关配置
        http://jingyan.baidu.com/article/ff42efa97b0f96c19e22023b.html
        http://jingyan.baidu.com/article/6b97984d9fe9e91ca2b0bf3c.html
*/

//phpinfo();

$data = array(
    'provider'=>'credentials',
    'UserName'=>'Administrator',
    'Password'=>888888,
    'RememberMe'=>FALSE,
    'PasswordIsEncrypted'=>FALSE
);
$data_string = json_encode($data);

//定义记录Cloud服务端返回的Session
$cookie_jar = tempnam('./tmp','CloudSession');
$loginurl = 'http://192.168.0.154/K3CloudServiceInterface/json/syncreply/Auth';
$ch = curl_init($loginurl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_COOKIEJAR,$cookie_jar);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string))
);

$result = curl_exec($ch);
curl_close($ch);

$array = json_decode($result,true);

echo '<pre>';print_r($data_string);
echo '<pre>';print_r($array);

/*
//View SEC_User（用户业务对象），16394 （Administrator管理员主键Id）//
$data = array(
    'CreateOrgId'=>0,
    'Number'=>'',
    'Id'=>'16394'
);

$data_string = json_encode($data);
$invokeurl =  'http://localhost/K3CloudServiceInterface/json/syncreply/SEC_User_View';
$ch = curl_init($invokeurl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ,'Content-Length: ' . strlen($data_string)
    )
);

$result = curl_exec($ch);
curl_close($ch);

$array = json_decode($result,true);

echo '<pre>';print_r($data_string);
echo '<pre>';print_r($array);
?>