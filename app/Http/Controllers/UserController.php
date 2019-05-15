<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
class UserController extends Controller
{
    //对称解密
    public function passwords(){
        $cipher = "AES-256-CBC";
        $tag = "zzxxcc";
        $options = OPENSSL_RAW_DATA;
        $iv = "qwertyuioplkjhgf";
        $plaintext = file_get_contents('php://input');
        echo "密文：".$plaintext;
        $original_plaintext=base64_decode($plaintext,true);
        $original_plaintext = openssl_decrypt($original_plaintext, $cipher, $tag, $options, $iv);

        echo "原文：".$original_plaintext;

    }
    //凯撒解密
    public function pwd(){
        $str = file_get_contents('php://input');
        $data = '';
        $length = strlen($str);
        for($i=0;$i<$length;$i++){
            $int = ord($str[$i])-3;
            $data .= chr($int);
        }
        echo "密文：".$str;
        echo "原文：".$data;
    }
    //非对称解密
    public function jyg(){
        $en_data = file_get_contents('php://input');
        echo "密文：".$en_data;echo "<br>";
        $en_data = base64_decode($en_data);
        $pp = openssl_pkey_get_public('file://'.storage_path('app/keys/public.pem'));
        openssl_public_decrypt($en_data,$xz_data,$pp);
        $xz_data = json_decode($xz_data,true);
        echo"原文：";print_r($xz_data);
    }
    //验证签名加密
    public function lc(){
        $str = file_get_contents("php://input");
        echo "json:".$str;echo "<br>";
        $reg_sgn = $_GET['sign'];
        $aa = openssl_get_publickey('file://'.storage_path('app/keys/public.pem'));
        $res = openssl_verify($str,base64_decode($reg_sgn),$aa);
        var_dump($res);
    }
    //注册
    public function reg(Request $request){
        header("Access-Control-Allow-Origin: *");
        $en_data = file_get_contents('php://input');
//        echo "密文：".$en_data;echo "<br>";
        $en_data = base64_decode($en_data);
        $pp = openssl_pkey_get_public('file://'.storage_path('app/keys/public.pem'));
        openssl_public_decrypt($en_data,$xz_data,$pp);
        $xz_data = json_decode($xz_data,true);
//        echo"原文：";print_r($xz_data);

        $email = DB::table('user')->where('user_email',$xz_data['user_email'])->first();
//        var_dump($email);die;
        if($email){
            $response=[
                'error'=>50001,
                'msg'=>'该邮箱已存在'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        };
        if($xz_data['user_pwd1']!=$xz_data['user_pwd2']){
            $response=[
                'error'=>50009,
                'msg'=>'密码不一致'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        };
        $password = password_hash($xz_data['user_pwd1'],PASSWORD_BCRYPT);
        //数据
        $data = [
            'user_name'=>$xz_data['user_name'],
            'user_pwd'=>$password,
            'user_email'=>$xz_data['user_email'],
        ];
        $uid = DB::table('user')->insert($data);
        if($uid){
            $response=[
                'error'=>0,
                'msg'=>'注册成功,快去注册吧！'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response=[
                'error'=>50002,
                'msg'=>'注册失败'

            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    }
    //登录
    public function doLogin(Request $request){
        header("Access-Control-Allow-Origin: *");
        $en_data = file_get_contents('php://input');
//        echo "密文：".$en_data;echo "<br>";
//        print_r($en_data);die;
        $en_data = base64_decode($en_data);
        $pp = openssl_pkey_get_public('file://'.storage_path('app/keys/public.pem'));
        openssl_public_decrypt($en_data,$xz_data,$pp);
        $xz_data = json_decode($xz_data,true);
//        echo"原文：";print_r($xz_data);
        $data = DB::table('user')->where('user_email',$xz_data['user_email'])->first();
        if($data){
            if(password_verify($xz_data['user_pwd'],$data->user_pwd)){
                $token = $this->loginToken();
                $redis_token_key = "login_token";
                Redis::set($redis_token_key,$token);
                Redis::expire($token,604800);
                $id = $data->user_id;
                $response=[
                    'error'=>0,
                    'msg'=>'登录成功',
                    'token'=>$token,
                    'id'=>$id
                ];
                return $response;
            }else{
                $response=[
                    'error'=>50003,
                    'msg'=>'密码不正确'
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'error'=>50004,
                'msg'=>'用户不存在'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
    //Token值
    public function loginToken(){
        $token = substr(sha1(md5(time() .Str::random(10))),3,20);
        return $token;
    }
    //个人中心
    public function my(){
      header("Access-Control-Allow-Origin: *");
      $id = $_GET['id'];
//      var_dump($id);die;
      $data = DB::table('user')->where('user_id',$id)->first();
      $response=[
          'error'=>0,
          'msg'=>'欢迎进入个人中心',
          'str'=>$data,
      ];
      $str = json_encode($response,JSON_UNESCAPED_UNICODE);
//      var_dump($str);
       return $str;
    }
}