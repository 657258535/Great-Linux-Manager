<?php
// echo "<pre>";
// print_r($_FILES);
// echo count($_FILES['file']['name']);
// exit;
error_reporting(0);
header('Access-Control-Allow-Origin: *');
date_default_timezone_set('Asia/Shanghai');
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

function base64_to_img( $base64_string, $output_file ) {
    $ifp = fopen( $output_file, "wb" ); 
    fwrite( $ifp, base64_decode( $base64_string) ); 
    fclose( $ifp ); 
    return( $output_file ); 
}
function mkdirs($dir, $mode = 0777){
if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
if (!mkdirs(dirname($dir), $mode)) return FALSE;
return @mkdir($dir, $mode);
}


?>