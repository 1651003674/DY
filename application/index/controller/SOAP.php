<?php
    function updateContract(){

    require_once ROOT_PATH.'tp5\extend\nusoap-0.9.5\lib\nusoap.php';
    $action = 'UpdateContract';
    $sendData = $data = [];
    // Config
    $client = new nusoap_client('http://192.168.4.100/KDWEBSERVICE/Contract.asmx?wsdl', 'wsdl');
    $client->soap_defencoding = 'UTF-8';
    $client->decode_utf8 = FALSE;

    $data['ContractNo']   = time();
    $data['ContractType']         = '销售合同';
    $data['uuid'] = $this->create_uuid();
    $data['ContractName'] = time();
    $data['Date'] = date('Y-m-d');
    $data['BillerID'] = 114;
    $data['Customer'] = [
        'ItemClassID'=>8,
        'Number' =>'0001',
        'Name'   => '广东顺德慧盛贸易有限公司-物流',
        'UUID'   => $this->create_uuid()
    ];

    $data['Currency'] = [
        'Number' =>'RMB',
        'Name'   => '人民币',
        'UUID'   => $this->create_uuid()
    ];

    $data['TotalAmountFor'] = 20000;

    $data['Department'] = [
        'Number' =>'010',
        'Name'   => 'QT上海',
        'UUID'   => $this->create_uuid()
    ];

    $data['Employee'] = [
        'Number' =>'114',
        'Name'   => '缪玥',
        'UUID'   => $this->create_uuid()
    ];

    //行明细
    $data['Body'] = [

        'Item' => [
            'UUID'   => $this->create_uuid(),
            'Number' =>'hshcp001.011.004.00027',
            'Name'   => '苯乙烯',
        ],

        'MeasureUnit' => [
            'UUID'   => $this->create_uuid(),
            'Number' =>'002',
            'Name'   => 'T(吨)',
        ],

        'Quantity' => '10',
        'PriceFor' => '20000',
        'TaxPriceFor' =>'20000',
        'AmountFor' =>'20000',
        'TaxFor' =>'0',
        'AmountIncludeTaxFor' =>'20000',
    ];
    $data['Plan'] = [
        'Date' =>date('Y-m-d'),
        'AmountFor'   => 20000,
    ];

    $data['Status'] = '未审核';

    $sendData['iAisID'] = 9;
    $sendData['strUser'] = 'administrator';
    $sendData['strPassword'] = 'Hsh.2017.$$$.Cn';
    $sendData['Data']['Contract'] = $data;
    $sendData['bCheckByUUID'] = false;
    $sendData['bAddNewOnly'] = false;

    // Callss
    $result = $client->call($action, $sendData);
    $err = $client->getError();
    test($client);
    test($result);
}

/**
 * @name 创建uuid
 * @param string $prefix
 * @return string
 */
function create_uuid($prefix = ""){ //可以指定前缀
    $str = md5(uniqid(mt_rand(), true));
    $uuid = substr($str,0,8) . '-';
    $uuid .= substr($str,8,4) . '-';
    $uuid .= substr($str,12,4) . '-';
    $uuid .= substr($str,16,4) . '-';
    $uuid .= substr($str,20,12);
    return $prefix . $uuid;
}