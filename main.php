<?php
/**
 * 过 Yii2 csrf验证
 *
 * 1.get登录页面，获取csrf_token
 * 2.构造post及cookie
 *
 * 手动处理cookie
 *
 * @author   Andy
 */

//登录信息
$account="******";
$password="******";
$userAgent= "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36";
//请求header
$requestHeader=[
    'Accept'=>"text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
    'Accept-Encoding'=>"gzip, deflate",
    'Accept-Language'=>"zh-CN,zh;q=0.9",
    'Connection'=>"keep-alive",
    'Host'=>"127.0.0.1",
    'Upgrade-Insecure-Requests'=>"1",
    'User-Agent'=>$userAgent,
];

//访问登录页面，初始化csrf_token
$loginUrl="http://127.0.0.1/login";
$res=getMsg($loginUrl,[],$requestHeader);

list($header, $body) = explode("\r\n\r\n", $res);
var_dump($header);


//提起header cookie中的csrf
$pattern="/_csrf(.*?);/";
preg_match($pattern,$header,$csrfCookie);
$nextCookie="_csrf".$csrfCookie[1];


//提取csrf_token隐藏域，或提取meta
$pattern="/name=\"_csrf\" value=\"(.*?)\">/";
preg_match($pattern,$body,$csrf);
$csrf=$csrf[1];




//构造post参数
$data=[
    '_csrf'=>$csrf,
    'username'=>$account,
    'password'=>$password,
];
$data=http_build_query($data);


$postUrl="http://127.0.0.1/login/index";
//请求时带上cookie
$ret=getMsg($postUrl,$data,$requestHeader,"post",$nextCookie);

list($header,$body)=explode("\r\n\r\n",$ret);
var_dump($header);

//重新解析新的set-cookie，处理identity身份信息及新的csrf
$pattern="/Set-Cookie: (.*?)HttpOnly/";
preg_match_all($pattern,$header,$matches);
var_dump($matches[1]);
$cookie=implode("",$matches[1]);
$cookie=str_replace(" path=/; ","",$cookie);
$nextCookie=trim($cookie,";");




//使用含有身份信息的cookie请求登录后页面，请求成功
$indexUrl="http://127.0.0.1/index/index";
$ret=getMsg($indexUrl,$data,$requestHeader,"get",$nextCookie);
var_dump($ret);


/**
 * @param $url
 * @param null $post post请求时数据
 * @param null $header 自定义header
 * @param string $method  请求方式
 * @param null $cookie 手动处理cookie
 * @return bool|string
 */
function getMsg($url, $post=null, $header=null, $method="get", $cookie=null){
    global $userAgent;
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
    curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION,false);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_HEADER,true);
//    curl_setopt($curl,CURLOPT_COOKIE,$cookie);
    curl_setopt($curl,CURLOPT_USERAGENT, $userAgent);
    if ($method=="post"){
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
    }
    $res=curl_exec($curl);
    curl_close($curl);
    return $res;
}