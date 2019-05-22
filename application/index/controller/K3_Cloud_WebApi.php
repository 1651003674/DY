<?php

defined('ByShopWWl') or exit('Access Invalid!');


/**
 * @name 登陆
 * @param $cloudUrl 服务器地址
 * @param $post_content 提交类容
 * @param $cookie_jar   链接标示
 * @return mixed
 */
function invoke_login($cloudUrl,$post_content,$cookie_jar)
{
    $loginurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.AuthService.ValidateUser.common.kdsvc';// 登录接口地址
    return invoke_post($loginurl,$post_content,$cookie_jar,TRUE);
}

//保存
function invoke_save($cloudUrl,$post_content,$cookie_jar)
{
    $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Save.common.kdsvc';
    return invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
}

//查询
function invoke_view($cloudUrl,$post_content,$cookie_jar)
{
    $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.View.common.kdsvc';
    return invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
}

//审核
function invoke_audit($cloudUrl,$post_content,$cookie_jar)
{
    $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Audit.common.kdsvc';
    return invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
}

//反审核
function invoke_unaudit($cloudUrl,$post_content,$cookie_jar)
{
    $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.UnAudit.common.kdsvc';
    return invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
}

//提交
function invoke_submit($cloudUrl,$post_content,$cookie_jar)
{
    $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Submit.common.kdsvc';
    return invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);// 执行Post 请求
}

/**
 * @name  Curl POST 请求操作
 * @param $url
 * @param $post_content
 * @param $cookie_jar
 * @param $isLogin 是否是 登录操作
 * @return mixed
 */
function invoke_post($url,$post_content,$cookie_jar,$isLogin)
{
    $ch = curl_init($url);

    // 定义请求头
    $this_header = array(
        'Content-Type: application/json',// 数据格式
        'Content-Length: '.strlen($post_content)// 数据长度
    );

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');// 设置请求模式 POST
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this_header);// 设置请求头
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_content);// 设置请求数据体
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 判断是否登录模式
    if($isLogin){
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);// 设置登录成功后 的 Cookie 会话标识 保存位置
    }
    else{
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);// 设置请求使用的会话 标识 文件
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);// 设置请求超时时间 30秒

    $result = curl_exec($ch);// 执行请求并获得结果
    curl_close($ch);// 关闭链接

    return $result;// 返回结果
}


/**
 * @name 构造Web API请求格式 得到 json 字符串
 * @param $args 参数
 * @return false|string
 */
function create_postdata($args) {
    $postdata = array(
        'format'=>1,
        'useragent'=>'ApiClient',
        'rid'=>create_guid(),
        'parameters'=>$args,// 参数列表 数组
        'timestamp'=>date('Y-m-d'),
        'v'=>'1.0'// 版本
    );

    return json_encode($postdata);
}


/**
 * @name 生成guid
 * @return string
 */
function create_guid() {
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45);// "-"
    $uuid = chr(123)// "{"
        .substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid,12, 4).$hyphen
        .substr($charid,16, 4).$hyphen
        .substr($charid,20,12)
        .chr(125);// "}"
    return $uuid;
}