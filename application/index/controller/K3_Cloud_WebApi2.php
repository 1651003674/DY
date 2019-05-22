<?php
$cloudUrl = "http://192.168.0.109/k3cloud/";
$data = [
    '55b828ecd6fc90',//帐套ID
    'Administrator', // 用户名
    '888888',// 密码
    2052//语言标识
];

$cookie_jar = tempnam('./tmp', 'CloudSession');// 登录后 Cookie 存放位置

//定义记录Cloud服务端返回的Session
$post_content = create_postdata($data);// 创建请求数据体

$result = invoke_login($cloudUrl, $post_content, $cookie_jar);//登入操作

// 结果解析
$newdata = json_decode($result, true);

// 判断登录是否成功
if ($newdata['LoginResultType'] == '1') {
    $condition = array();
    $condition['is_erp'] = '0';
//分批，每批处理100个会员，最多处理5W个会员
    for ($i = 0; $i < 500; $i++) {
        if ($_break) break;


        $member_list = Model('member') -> getMemberList($condition, '*', '', '', 100);

        if (empty($member_list))break;

        foreach ($member_list as $member_info) {
            $save_arr = [];
            $save_arr['Model']['FCreateOrgId']['FNumber'] = '100';
            $save_arr['Model']['FNumber'] = '04.' . $member_info['member_id'];
            $save_arr['Model']['FUseOrgId']['FNumber'] = '100';
            $save_arr['Model']['FName'] = $member_info['member_name'];
            $save_arr['Model']['FGroup']['FNumber'] = '04';
            if($member_info['member_mobile_bind']=='1'){
                $save_arr['Model']['FTEL']=$member_info['member_mobile'];
            }
            $data_model = json_encode($save_arr);
            $data = array('BD_Customer_All', //业务对象标识FormId
                $data_model//具体Json字串
            );
            $post_content = create_postdata($data);
            $result = invoke_save($cloudUrl, $post_content, $cookie_jar);
            $one_data = json_decode($result, true);

            if ($one_data['Result']['ResponseStatus']['IsSuccess'] == 'true') {
                //执行提交后审核动作
                $submit_arr = array();
                $submit_arr['Numbers'] = '04.' . $member_info['member_id'];
                $submit_model = json_encode($submit_arr);
                $submit_data = array('BD_Customer_All', $submit_model);
                $submit_post_content = create_postdata($submit_data);
                //提交
                invoke_submit($cloudUrl, $submit_post_content, $cookie_jar);
                //审核
                invoke_audit($cloudUrl, $submit_post_content, $cookie_jar);
                Model('member') -> editMember(array('member_id' => $member_info['member_id']), array('is_erp' => '1'));
            } else {
                $this -> log('会员同步失败:' . $member_info['member_id']);
                $_break = true;
                break;
            }
        }
    }
}