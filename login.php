<?php
class Online_Query{
    var $header=["User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 MicroMessenger/7.0.20.1781(0x6700143B) NetType/WIFI MiniProgramEnv/Windows WindowsWechat/WMPF WindowsWechat(0x63090c33)XWEB/14185",
    "Upgrade-Insecure-Requests: 1",
    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/wxpic,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
    "Sec-Fetch-Site: same-site",
    "Sec-Fetch-Mode: navigate",
    "Sec-Fetch-Dest: document",
    "Accept-Encoding: gzip, deflate, br",
    "Accept-Language: zh-CN,zh;q=0.9",
    "Sec-Fetch-User: ?1"];
    var $id;
    var $token;
    function __construct($id=null,$token=null)
    {
        $this->id=$id;
        $this->token=$token;
        array_push($this->header,"token: ".$token);
    }

    function getToken(){return $this->token;}

    function getId(){return $this->id;}

    function getHeader(){return $this->header;}

    /**
     * 向服务器请求
     * 获取本科院专业
     * 
     * @param mixed $year 年份
     * @return string|false 返回有效，返回json，无效，false
     */
    function getMajorList($year)
    {
        //array_push($header,"token: ".$token);

        $major_list=httpRequest("https://jwyd.biem.edu.cn/bjjjglzyxyhd/annualMajorList?academyId=4698B16327FE4F41A7813AFF236F1CF8&grade=".$year,header:$this->header,post:false);
        if($major_list[0]==0)
        {
            return false;
        }
        else
        {
            return $major_list[1];
        }
    }

    /**
     * 向服务器请求
     * 获取专业信息
     * 
     * @param mixed $year 年份
     * @param string $majorId 学科ID，建议先通过GetMajorList()函数获取后再调用
     * @return string|false 返回有效，返回json，无效，false
     */
    function getFosterDetail($year,string $majorId)
    {
        //array_push($header,"token: ".$token);

        $detail=httpRequest("https://jwyd.biem.edu.cn/bjjjglzyxyhd/teacher/majorFosterDetail?majorId=".$majorId."&gardeId=".$year,header:$this->header,post:false);
        if($detail[0]==0)
        {
            return false;
        }
        else
        {
            return $detail[1];
        }
    }
}
$header=["User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 MicroMessenger/7.0.20.1781(0x6700143B) NetType/WIFI MiniProgramEnv/Windows WindowsWechat/WMPF WindowsWechat(0x63090c33)XWEB/14185",
    "Upgrade-Insecure-Requests: 1",
    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/wxpic,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
    "Sec-Fetch-Site: same-site",
    "Sec-Fetch-Mode: navigate",
    "Sec-Fetch-Dest: document",
    "Accept-Encoding: gzip, deflate, br",
    "Accept-Language: zh-CN,zh;q=0.9",
    "Sec-Fetch-User: ?1"];

/**
 * 获取用户登录token实例
 * 
 * @param string $userid 用户ID
 * @return Online_Query|false 当获取成功时，返回拥有token的Online_Query类型
 */
function TryLogin($userid)
{
    global $header;
    /*$opts = array(  
        'http' => array (
            'method' => "POST",
            'timeout' => 5, // 设置超时时间为60秒
            
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
            . "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data
        )
    );*/
    //echo($data);

    //echo $opts;
    //$context = stream_context_create($opts);
    $result= httpRequest("https://tyfw.biem.edu.cn/run/authgate/xcxzz?token=".$userid."&url=https%3A%2F%2Fjwyd.biem.edu.cn%2Fbjjjglzyxyhd%2FxcxBjjjgl%3FtoMenu%3Djs_pyfa_zy",[] ,$header, post:false,allow_redirect:false, cookie:"token=".$userid.";tokenvalue=".$userid.";tokenname=token;", output_header:true);
    //echo $result[1];
    if($result[0]==0)
    {
        return false;//Echo_Response("server_not_found_info");
    }
    else
    {
        list($head, $body) = explode("\r\n\r\n", $result[1]);
        // 解析COOKIE
        preg_match("/_zte_cid_=(.*); Path=/", $head, $ztecid);
        preg_match("/_zte_sid_=(.*); Path=/", $head, $ztesid);
        $ztecid=trim($ztecid[1]);
        $ztesid=trim($ztesid[1]);
        $cookie="_zte_cid_=".$ztecid.";_zte_sid_=".$ztesid;
        //请求的时候headers 带上cookie就可以了
        $result_second=httpRequest("https://jwyd.biem.edu.cn/bjjjglzyxyhd/xcxBjjjgl?toMenu=js_pyfa_zy",post:false,allow_redirect:false,header:$header,output_header:true,cookie:$cookie);
        //Echo_Response(Custom_Content:$to);
        //echo($result_second[1]."\n");
        preg_match("/Location:(.*)/",$result_second[1],$location);
        $location=trim($location[1]);
        //echo($location."\n");
        $result_third=httpRequest($location,header:$header,cookie:"token=".$userid.";tokenname=token;tokenvalue=".$userid.";".$cookie,output_header:false,allow_redirect:false,post:false);

        @preg_match("/window\.location\.href='(.*)';/",$result_third[1],$redirect);
        
        if(count($redirect)==0)
        {
            return false;
        }

        $result_forth=httpRequest($redirect[1],allow_redirect:true,header:$header,output_header:true);
        
        preg_match("/token=(.*)&userType/",$result_forth[1],$token);//Login finish at here.

        $token=trim($token[1]);

        //array_push($header,"token: ".$token);
        //echo(var_dump($header_second));
        //$class_list=httpRequest("https://jwyd.biem.edu.cn/bjjjglzyxyhd/annualMajorList",params:["academyId"=>"4698B16327FE4F41A7813AFF236F1CF8","grade"=>"2024"],header:$header_second,post:false);
        //header("token:".$token);
        //header("Warning(Just-Message): token-is-a-dangerous-thing,-donot-share-it-to-others.");
        //return $token;
        return new Online_Query($userid,$token);
    }
}

/**
 * 向服务器请求
 * 获取本科院专业
 * 
 * @param Online_Query $query 请求实例
 * @param mixed $year 年份
 * @return string|false 返回有效，返回json，无效，false
 */
function GetMajorList(Online_Query $query,$year)
{
    $header=$query->getHeader();
    //array_push($header,"token: ".$token);

    $major_list=httpRequest("https://jwyd.biem.edu.cn/bjjjglzyxyhd/annualMajorList?academyId=4698B16327FE4F41A7813AFF236F1CF8&grade=".$year,header:$header,post:false);
    if($major_list[0]==0)
    {
        return false;
    }
    else
    {
        return $major_list[1];
    }
}

/**
 * 向服务器请求
 * 获取专业信息
 * 
 * @param Online_Query $query 请求实例
 * @param mixed $year 年份
 * @param string $majorId 学科ID，建议先通过GetMajorList()函数获取后再调用
 * @return string|false 返回有效，返回json，无效，false
 */
function GetFosterDetail(Online_Query $query,$year,string $majorId)
{
    
    $header=$query->getHeader();

    //array_push($header,"token: ".$token);

    $detail=httpRequest("https://jwyd.biem.edu.cn/bjjjglzyxyhd/teacher/majorFosterDetail?majorId=".$majorId."&gardeId=".$year,header:$header,post:false);
    if($detail[0]==0)
    {
        return false;
    }
    else
    {
        return $detail[1];
    }
}
?>