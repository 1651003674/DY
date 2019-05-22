<?php
namespace app\index\controller;
use app\Common\Cloud_webapi_client;

class Index
{
    public function index()
    {

    }

    // http://192.168.0.115:81/index.php?s=index/index/hello
    public function hello($name = 'ThinkPHP5')
    {

        $request = $_SERVER;
        $path =  __DIR__.DIRECTORY_SEPARATOR.'paramdata.txt';
//        if (!is_file($path)) mkdir($path);
        file_put_contents($path,"\n\n",FILE_APPEND);
        file_put_contents($path,"[ ".date('Y-m-d H:i:s',time())." ]"."\n",FILE_APPEND);

        foreach ($request as $k=>$value)
        {

            file_put_contents($path,$k." = ".$value."\n",FILE_APPEND);
        }

        return json($request);
    }
}
