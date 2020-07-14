<?php
/**
 * 过 Yii2 csrf验证
 *
 * 1.get登录页面，获取csrf_token
 * 2.构造post及cookie
 *
 * CURLOPT_COOKIEJAR 保存Response Set-Cookie
 * CURLOPT_COOKIEFILE Request带上文件中的cookie信息
 * @author   Andy
 */




//登录信息
$account="******";
$password="******";
$userAgent= "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36";
//通用请求header
$requestHeader=[
    'User-Agent'=>$userAgent
];

$loginUrl="http://127.0.0.1/login";

$res=getMsg($loginUrl,[],$requestHeader);
//访问登录页面，初始化csrf_token
list($header, $body) = explode("\r\n\r\n", $res);
var_dump($header);

//提取csrf_token隐藏域，或提取meta
$pattern="/name=\"_csrf\" value=\"(.*?)\">/";
preg_match($pattern,$body,$csrf);

$csrf=$csrf[1];

//构造post表单参数
$data=[
    '_csrf'=>$csrf,
    'username'=>$account,
    'password'=>$password,
];
$data=http_build_query($data);

//登录post
$postUrl="http://127.0.0.1/login/index";
$ret=getMsg($postUrl,$data,$requestHeader,"post");

list($header,$body)=explode("\r\n\r\n",$ret);
var_dump($header);






//不使用CURLOPT_FOLLOWLOCATION   手动访问登录后页面
$indexUrl="http://127.0.0.1/index/index";
$ret=getMsg($indexUrl,$data,$requestHeader,"get");
var_dump($ret);


/**
 * @param $url
 * @param null $post post请求时数据
 * @param null $header 自定义header
 * @param string $method  请求方式
 * @return bool|string
 */
function getMsg($url, $post=null, $header=null, $method="get"){
    global $userAgent;
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
    //在请求时使用指定header
    curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION,false);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    //在请求时使用指定cookie
    curl_setopt($curl,CURLOPT_COOKIEFILE,"./cookies.txt");
    //请求完成时将set-cookie写入指定文件
    curl_setopt($curl,CURLOPT_COOKIEJAR,"./cookies.txt");
    curl_setopt($curl,CURLOPT_HEADER,true);
    curl_setopt($curl,CURLOPT_USERAGENT, $userAgent);
    if ($method=="post"){
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
    }
    $res=curl_exec($curl);
    curl_close($curl);
    if ($res!=false){
        return $res;
    }else{
        exit("curl失败");
    }
}