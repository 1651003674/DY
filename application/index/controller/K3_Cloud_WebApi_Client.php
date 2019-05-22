<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/2
 * Time: 10:32
 */

//namespace app\index\controller;


class K3_Cloud_WebAPI_Client
{
    private $cloudUrl = "http://118.122.113.114:8082/k3cloud/";
    private $data = [
//        '5c9317a12b6d00',//帐套ID
//        '5c1e8e9cff9168',//帐套ID 南海
        '5c87101bd87fa8',// 东源
        'administrator', // 用户名
        '888888',// 密码
        2052//语言标识
    ];

    public $cookie_jar = null;// 登录后 Cookie 存放位置

    function __construct()
    {
        $this->cookie_jar = tempnam('./tmp', 'CloudSession');// 登录后 Cookie 存放位置

    }

    public function  getList()
    {
        $this->login();
//print_r($this->cookie_jar);die();
        $save_arr =[
                "FormId"=>"STK_InStock",
                "FieldKeys"=>"FID",
                "FilterString"=>"",
                "OrderString"=>"",
                "TopRowCount"=>"0",
                "StartRow"=>"0",
                "Limit"=>"10"
        ];


        $data_model = json_encode(["data"=>$save_arr]);//  编码成 json 格式

        $data = $data_model;
//        $data = array('SAL_OUTSTOCK', //业务对象标识FormId
//            $data_model//具体Json字串
//        );
//        $post_content = $this->create_postdata($data);// 调用本类的方法 创建请求数据

        $output = $this->invoke_list($this->cloudUrl,$data_model,$this->cookie_jar);

        var_dump(json_decode($output,1));
    }

    public function _member_save()
    {

        $save_arr =[
            [
            "FormId"=>"SAL_OUTSTOCK",
            "FieldKeys"=>"",
            "FilterString"=>"",
            "OrderString"=>"",
            "TopRowCount"=>"0",
            "StartRow"=>"0",
            "Limit"=>"10"
            ]
        ];

        $data_model = json_encode($save_arr);//  编码成 json 格式

        $data = array('BD_Customer_All', //业务对象标识FormId
            $data_model//具体Json字串
        );
        $post_content = $this->create_postdata($data);// 调用本类的方法 创建请求数据


        $result = $this->invoke_save($this->cloudUrl, $post_content, $this->cookie_jar);// 执行数据保存操作
        var_dump($result);
        die();
        $condition = array();
        $condition['is_erp'] = '0';
        //分批，每批处理100个会员，最多处理5W个会员
        $_break = false;// 定义失败标识

        for ($i = 0; $i < 500; $i++) {
            if ($_break) break;

//            $member_list = Model('member')
//                -> getMemberList(
//                    $condition, // 查询条件
//                    '*',// 获取的字段
//                    '',
//                    '',
//                    100 //获取数据条数
//                );// 查询获取会员列表

            // 如果没有结果 跳出循环
            if (empty($member_list)) break;

            //  循环 处理 查询到的会员数据
            foreach ($member_list as $member_info) {
//                $save_arr = [];
//                $save_arr['Model']['FCreateOrgId']['FNumber'] = '100';// 数据条数
//                $save_arr['Model']['FNumber'] = '04.' . '2455';// 会员id
//                $save_arr['Model']['FUseOrgId']['FNumber'] = '100';// 数据条数
//                $save_arr['Model']['FName'] = 'huangchao';// 会员名
//                $save_arr['Model']['FGroup']['FNumber'] = '04';// 会员分组标识
//
//                if($member_info['member_mobile_bind']=='1'){ // 如果会员有绑定 手机
//                    $save_arr['Model']['FTEL']=13980872454;// 同步手机
//                }
                $save_arr =[
                    "FormId"=>"",
                    "FieldKeys"=>"",
                    "FilterString"=>"",
                    "OrderString"=>"",
                    "TopRowCount"=>"0",
                    "StartRow"=>"0",
                    "Limit"=>"0"
                ];
                $data_model = json_encode($save_arr);//  编码成 json 格式

                $data = array('STK_InStock', //业务对象标识FormId
                    $data_model//具体Json字串
                );
                $post_content = $this->create_postdata($data);// 调用本类的方法 创建请求数据


                $result = $this->invoke_save($this->cloudUrl, $post_content, $this->cookie_jar);// 执行数据保存操作
                die();
                $one_data = json_decode($result, true);// 解析保存请求执行结果

                if ($one_data['Result']['ResponseStatus']['IsSuccess'] == 'true') {//  如果保存执行成功
                    //执行提交后审核动作
                    $submit_arr = array();
                    $submit_arr['Numbers'] = '04.' . '2455';
                    $submit_model = json_encode($submit_arr);
                    $submit_data = ['BD_Customer_All', $submit_model];//

                    // 创建请求数据
                    $submit_post_content = create_postdata($submit_data);

                    //提交
                    invoke_submit($cloudUrl, $submit_post_content, $cookie_jar);
                    //审核
                    invoke_audit($cloudUrl, $submit_post_content, $cookie_jar);

                    Model('member')
                        -> editMember(
                            ['member_id' => $member_info['member_id']],// 锁定的条件
                            ['is_erp' => '1']// 修改的数据
                        );// 修改会员信息状态 为已同步
                } else {
                    $this -> log('会员同步失败:' . $member_info['member_id']);
                    $_break = true;// 失败时 设置为true
                    break;
                }
            }
        }
    }

    public function login()
    {
        //定义记录Cloud服务端返回的Session
        $post_content = $this->create_postdata($this->data);// 创建请求数据体

        $result = $this->invoke_login($this->cloudUrl, $post_content, $this->cookie_jar);//登入操作

        // 结果解析
        $newdata = json_decode($result, true);

        // 判断登录是否成功
        if ($newdata['LoginResultType'] == '1') {
            return true;
        }else{
            return false;
        }
    }

    /**
     * @name 登陆 Cloud 服务器
     * @param $cloudUrl 服务器地址
     * @param $post_content 提交类容
     * @param $cookie_jar   链接标示
     * @return mixed
     */
    private function invoke_login($cloudUrl,$post_content,$cookie_jar)
    {
        $loginurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.AuthService.ValidateUser.common.kdsvc';// 登录接口地址
        return $this->invoke_post($loginurl,$post_content,$cookie_jar,TRUE);
    }


    /**
     * @name 保存
     * @param $cloudUrl 服务器地址
     * @param $post_content 请求数据体
     * @param $cookie_jar 链接标识
     * @return mixed
     */
    private function invoke_save($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Save.common.kdsvc';
        return $this->invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }


    /**
     * @name 查询数据 单条
     * @param $cloudUrl 服务器地址
     * @param $post_content 请求数据体
     * @param $cookie_jar 链接标识
     * @return mixed
     */
    private function invoke_view($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.View.common.kdsvc';
        return $this->invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }


    /**
     * @name 查询获取列表
     * @param $cloudUrl
     * @param $post_content
     * @param $cookie_jar
     * @return mixed\
     */
    private function invoke_list($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.ExecuteBillQuery.common.kdsvc';
        return $this->invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }


    /**
     * @name 审核数据 单条
     * @param $cloudUrl
     * @param $post_content
     * @param $cookie_jar
     * @return mixed
     */
    private function invoke_audit($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Audit.common.kdsvc';
        return $this->invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }


    /**
     * @name 反审核
     * @param $cloudUrl
     * @param $post_content
     * @param $cookie_jar
     * @return mixed
     */
    private function invoke_unaudit($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.UnAudit.common.kdsvc';
        return $this->invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }


    /**
     * @name 提交
     * @param $cloudUrl
     * @param $post_content
     * @param $cookie_jar
     * @return mixed
     */
    private function invoke_submit($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Submit.common.kdsvc';
        return $this->invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);// 执行Post 请求
    }

    /**
     * @name  Curl POST 请求操作
     * @param $url
     * @param $post_content
     * @param $cookie_jar
     * @param $isLogin 是否是 登录操作
     * @return mixed
     */
    private function invoke_post($url,$post_content,$cookie_jar,$isLogin)
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
        var_dump($url,$post_content);
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
    private function create_postdata($args) {
        $postdata = array(
            'format'=>1,
            'useragent'=>'ApiClient',
            'rid'=>$this->create_guid(),
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
    private function create_guid() {
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
}
// var_dump((new K3_Cloud_WebApi_Client())->login());
 var_dump((new K3_Cloud_WebApi_Client())->getList());