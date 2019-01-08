<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/23
 * Time: 16:15
 */

namespace app\home\controller;
use think\Controller;
class BaseController extends Controller
{
    public function _initialize(){
        $ip=request()->ip();
        $attr=array("reg","reg_two","reg_three");
        $method=request()->action();
        if(!in_array($method,$attr) && $ip!="61.164.143.13"){
            if (!session('user.uid')) {
                $this->redirect('Index/login');
            }
        }
        /**
         * 头文件信息 头像 昵称 账号介绍 共享 其他页面
         */
        $uid = session('user.uid');
        $headerinfo = db('users')->field('uid,avatar,nickname,account_introduce,email,mobile,real_name,id_card,media_type,media_area,province,city')->where("uid", $uid)->find();
        $avatar=$headerinfo['avatar'];
        $nickname=$headerinfo['nickname'];
        $account_introduce=$headerinfo['account_introduce'];
        $email=$headerinfo['email'];

        //在主表中查询
        $eastlady_user_info = db('eastlady_user')->field('mobile')->where("uid", $uid)->find();;
        $mobile=$eastlady_user_info['mobile'];
        $mobile_ = preg_replace('/(\d{5})\d{4}(\d{2})/', '$1****$2', $mobile);
        //substr_replace($mobile, '****', 5, 4);   substr($mobile, 0, 5).'****'.substr($mobile, 9)
        $real_name = mb_substr($headerinfo['real_name'], 0, 1,'utf-8').'****';
        $province = $headerinfo['province'];
        $city = $headerinfo['city'];
        $area = get_city($province)['name']." ".get_city($city)['name'];

        $id_card=$headerinfo['id_card'];
        $uid=$headerinfo['uid'];
        $media_area=$headerinfo['media_area'];
        //媒体类型 1：个人，2：媒体，3：国家机构，4：企业，5：其他组织
        $media_type=$headerinfo['media_type'];
        switch ( $media_type ) {
            case  1 :
                $this->assign('media_type',"个人"); ;
                break;
            case  2 :
                $this->assign('media_type',"媒体"); ;
                break;
            case  3 :
                $this->assign('media_type',"国家机构"); ;
                break;
            case  4 :
                $this->assign('media_type',"企业"); ;
                break;
            case  5 :
                $this->assign('media_type',"其他组织"); ;
                break;
        }
        $this->assign('headerinfo',$headerinfo);
        $this->assign('account_introduce',$account_introduce);
        $this->assign('nickname',$nickname);
        $this->assign('avatar',$avatar);
        $this->assign('email',$email);
        $this->assign('mobile',$mobile);//绑定手机号
        $this->assign('mobile_',$mobile_);//联系方式
        $this->assign('real_name',$real_name);
        $this->assign('id_card',$id_card);
        $this->assign('uid',$uid);
        $this->assign('media_area',$media_area);
        $this->assign('area',$area);

    }
    //获得标签名称
   public function getCatname($id){
        $cat=db("arctype")->where(array('id'=>$id))->find();
        return $cat['typename'];
    }
    protected static function showJson($data=array(),$status=1,$msg=""){
        header('Content-Type:application/json');
        $result['code']=$status;
        $result['msg']=$msg;
        $result['data']=$data;
        return json_encode($result);
    }

}