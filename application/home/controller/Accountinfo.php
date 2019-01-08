<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 18:11
 */

namespace app\home\controller;

use think\Controller;
use think\Db;
use think\Request;

class Accountinfo extends BaseController
{
    protected $user;
    public function _initialize()
    {
        parent::_initialize();
        $this->user=session("user");
    }
    /**
     * 账号状态
     */
    public function account_status()
    {
//        $uid = session('user.uid');
        return $this->fetch("");
    }

    /**
     * 账号信息
     *
     */
    public function account_info()
    {
        $uid = session('user.uid');
        $uid = 18;
        /**
         * 头文件信息 头像 昵称 账号介绍 共享 其他页面
         */
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
        return $this->fetch("");
    }

    /**
     * 修改账号昵称
     * 2018年4月3日 by wb
     *
     */
    public function fixNickname()
    {
        $uid = session('user.uid');
        $nickname = input('nickname');
        $click_type = input('click_type');
        if(empty($nickname)){
            $msg = "所填信息不能为空！";
            exit(parent::showJson("NULL_ERROR", 405, $msg));
        }
        $up_data['nickname'] = $nickname;
        if ($click_type == "fixNickname") {
            $click_type = 1;
        }
        $ret_uid_info = db('account_info_update')->where(array('uid' => $uid,'click_type'=>$click_type))->select();
        if (empty($ret_uid_info)) {
            $tmp = !empty($nickname);
            if ($tmp) {
                $ret_nickname = db('users')->field('nickname')->where(array('uid' => $uid))->find();
                if ($ret_nickname['nickname'] == $nickname) {
                    $msg = "信息未更改！";
                    exit(parent::showJson("UPDATE_ERROR", 404, $msg));
                } else {
                    $ret = db('users')->where(array('uid' => $uid))->update($up_data);
                    $up_account_data['uid'] = $uid;
                    $up_account_data['dateline'] = time();
                    $up_account_data['click_type'] = $click_type;
                    $ret = db('account_info_update')->insert($up_account_data);
                    $msg = "修改成功！";
                    exit(parent::showJson($ret, 200, $msg));
                }
            } else {
                $msg = "昵称不合法！请再次填写昵称！";
                exit(parent::showJson("NICKNAME_ERROR", 404, $msg));
            }
        } else {
            foreach ($ret_uid_info as $v) {
                if ($v['uid'] == $uid && $v['click_type'] == $click_type) {
                    $tmptime = time() - $v['dateline'] > 30 * 24 * 60 * 60;
                    //如果是超过 30天 可以修改
                    if ($tmptime) {
                        $ret = db('users')->where(array('uid' => $uid))->update($up_data);
                        $up_account_data['dateline'] = time();
                        $up_account_data['click_type'] = $click_type;
                        $ret = db('account_info_update')->where(array('uid' => $uid))->update($up_account_data);
                        $msg = "间隔30天修改成功！";
                        exit(parent::showJson($ret, 200, $msg));
                    } else {
                        //不能修改
                        $msg = "30天內只能修改一次！";
                        exit(parent::showJson("LIMINT_ERROR", 504, $msg));

                    }

                }
            }
        }

    }

    /**
     * 修改账号介绍
     * 2018年4月3日 by wb
     *
     */
    /**备份用 需求后期可能会作更改***/
    public function fixAccountIntroducebak()
    {
        $uid = session('user.uid');
        $AccountIntroduce = input('AccountIntroduce');
        $click_type = input('click_type');
        $up_data['account_introduce'] = $AccountIntroduce;

        if ($click_type == "fixAccountIntroduce") {
            $click_type = 2;
        }
        $ret_uid_info = db('account_info_update')->where(array('uid' => $uid))->select();
        $tmp = !empty($AccountIntroduce);
        if ($tmp) {
            $uid = session('user.uid');
            $ret_account_introduce = db('users')->field('account_introduce')->where(array('uid' => $uid))->find();


            if ($ret_account_introduce['account_introduce'] == $AccountIntroduce) {
                $msg = "信息未更改！";
                exit(parent::showJson("UPDATE_ERROR", 404, $msg));
            } else {
                $ret = db('users')->where(array('uid' => $uid))->update($up_data);
                $msg = "修改成功！";
                exit(parent::showJson($ret, 200, $msg));
            }
        } else {
            $msg = "账号介绍不合法！请再次填写账号介绍！";
            exit(parent::showJson("INTRODUCE_ERROR", 404, $msg));
        }
    }
    /**备份用需求后期可能会作更改***/
    public function fixAccountIntroduce()
    {
        $uid = session('user.uid');
        $AccountIntroduce = input('AccountIntroduce');
        if(empty($AccountIntroduce)){
            $msg = "所填信息不能为空！";
            exit(parent::showJson("NULL_ERROR", 405, $msg));
        }
        $click_type = input('click_type');
        $up_data['account_introduce'] = $AccountIntroduce;
        if ($click_type == "fixAccountIntroduce") {
            $click_type = 2;
        }
        $ret_uid_info = db('account_info_update')->where(array('uid' => $uid,'click_type'=>$click_type))->select();
        $ret_account_introduce = db('users')->field('account_introduce')->where(array('uid' => $uid))->find();

        if (empty($ret_uid_info)) {
            $tmp = !empty($AccountIntroduce);
            if ($tmp) {
                if ($ret_account_introduce['account_introduce'] == $AccountIntroduce) {
                    $msg = "信息未更改！";
                    exit(parent::showJson("UPDATE_ERROR", 404, $msg));
                } else {
                    $ret = db('users')->where(array('uid' => $uid))->update($up_data);
                    $up_account_data['uid'] = $uid;
                    $up_account_data['dateline'] = time();
                    $up_account_data['click_type'] = $click_type;
                    $ret = db('account_info_update')->insert($up_account_data);
                    $msg = "修改成功！";
                    exit(parent::showJson($ret, 200, $msg));
                }
            } else {
                $msg = "账号介绍不合法！请再次填写账号介绍！";
                exit(parent::showJson("INTRODUCE_ERROR", 404, $msg));
            }
        } else {
            foreach ($ret_uid_info as $v) {
                if ($v['uid'] == $uid && $v['click_type'] == $click_type) {
                    $tmptime = time() - $v['dateline'] > 30 * 24 * 60 * 60;
                    //如果是超过 30天 可以修改
                        if ($tmptime) {
                            $ret = db('users')->where(array('uid' => $uid))->update($up_data);
                            $up_account_data['dateline'] = time();
                            $up_account_data['click_type'] = $click_type;
                            $ret = db('account_info_update')->where(array('uid' => $uid))->update($up_account_data);
                            $msg = "间隔30天修改成功！";
                            exit(parent::showJson($ret, 200, $msg));
                        } else {
                            //不能修改
                            $msg = "30天內只能修改一次！";
                            exit(parent::showJson("LIMINT_ERROR", 504, $msg));

                        }
                }
            }
        }
    }

    /**
     * 头像做裁剪处理
     * 2018年4月11日 by metal
     *
     */
    public function Index()
    {
        $Intention = trim($_POST['Intention'])?trim($_POST['Intention']):trim($_GET['Intention']);
        if ($Intention == '') {
            $json_result = array(
                'ResultCode' => 500,
                'Message' => '系統錯誤',
                'Url' => ''
            );
            if($_GET['Intention']){
                echo 'jsonpCallback('.json_encode($json_result).')';
            }
            elseif($_POST['Intention']){
                echo json_encode($json_result);
            }
            exit;
        }
        $this->$Intention();
    }
    /**
     * @desc  上传图片
     */
    private function SaveAvatar()
    {
        $ImgBaseData = trim($_POST['Img']);
        $savePath ='public/uploads/avatar/'.date('Ymd').'/';

//        vendor('Common.Common');
//        include_once './vendor/Common/Common.php';
//        require_once './vendor/Common/Common.php';
//        include_once './vendor/Common/Common.php';
        include_once '../vendor/Common/Common.php';
        // 实例化
        $ImageUrl = SendToImgServ($savePath,$ImgBaseData);
        $Data['avatar'] = $ImageUrl ? $ImageUrl : '';
        //若是历史记录 详情 传的图片给他 直接存数据库
//        $mark = I('mark');
//        $rid = I('rid');
//        if($mark=='history'){
//            $tmp = session('userInfo');
//            $uid=$tmp['uid'];
//            $data['rid']= $rid;
//            $data['uid']= $uid;
//            $data['img_url'] = '/'.$Data['Avatar'];
//            M('ImgVideoUse')->add($data);
//        }
        if ($Data['avatar'] !==''){
            $data = '/'.$Data['avatar'];
            $msg = '上传成功!';
            exit(parent::showJson($data,200,$msg));
//            $result_json = array('ResultCode'=>200,'Message'=>'上传成功！','url'=>'/'.$Data['avatar']);
        }else{
            $msg = '上传失败!';
            exit(parent::showJson('',102,$msg));
//            $result_json = array('ResultCode'=>102,'Message'=>'上传失败！');
        }
//        EchoResult($result_json);
//        exit;
    }
    /**账号状态 修改密码 方法
     * 2018年4月3日 by wb
     * @return mixed
     */
    public function upload()
    {
        $type = input('type', 1);
        // 获取上传文件表单字段名
        $fileKey = array_keys(request()->file());
        // 获取表单上传文件
        $file = request()->file($fileKey['0']);
        //根据type判断是前台上传的图片还是后台上传的图片
        if ($type == 1) {
            // 移动到框架应用根目录/public/uploads/home/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'home');
        } else {
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        }

        if ($info) {
            $result['code'] = 1;
            $result['msg'] = '图片上传成功!';
            $path = str_replace('\\', '/', $info->getSaveName());
            if ($type == 1) {
                $uid = session('user.uid');
                $result['data'] = '/uploads/home/' . $path;
                $data2["avatar"] = $result['data'];

                $up_ret = db("users")->where("uid", $uid)->update($data2);
            } else {
                $result['url'] = '/uploads/' . $path;
            }
            return $result;
        } else {
            // 上传失败获取错误信息
            $result['code'] = 0;
            $result['msg'] = '图片上传失败!';
            $result['data'] = '';
            return $result;
        }
    }
    public function saveUrl($url){
        $uid = session('user.uid');
        $data2["avatar"] = $url;
        $up_ret = db("users")->where("uid", $uid)->update($data2);
        if ($up_ret){
            $result['code'] = 1;
            $result['msg'] = '图片保存成功!';
            $result['data'] = '';
            return $result;
        }else{
            // 上传失败获取错误信息
            $result['code'] = 0;
            $result['msg'] = '图片保存失败!';
            $result['data'] = '';
            return $result;
        }
    }


//    public function upload()
//    {
//        $type = input('type', 1);
//        // 获取上传文件表单字段名
//        $fileKey = array_keys(request()->file());
//        // 获取表单上传文件
//        $file = request()->file($fileKey['0']);
//        //根据type判断是前台上传的图片还是后台上传的图片
//        if ($type == 1) {
//            // 移动到框架应用根目录/public/uploads/home/ 目录下
//            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'home');
//        } else {
//            // 移动到框架应用根目录/public/uploads/ 目录下
//            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
//        }
//
//        if ($info) {
//            $result['code'] = 1;
//            $result['msg'] = '图片上传成功!';
//            $path = str_replace('\\', '/', $info->getSaveName());
//            if ($type == 1) {
//                $uid = session('user.uid');
//                $result['data'] = '/uploads/home/' . $path;
//                p($result['data']);exit;
//                $data2["avatar"] = $result['data'];
//                $click_type = input('click_type');
//                if ($click_type == "fixEmail") {
//                    $click_type = 3;
//                }
//                $up_ret = db("users")->where("uid", $uid)->update($data2);
//                $ret_uid_info = db('account_info_update')->where(array('uid' => $uid,'click_type'=>$click_type))->select();
//
//                if (empty($ret_uid_info)) {
//                    $tmp = !empty($result['data']);
//                    if ($tmp) {
//                        $ret_email = db('users')->field('avatar')->where(array('uid' => $uid))->find();
//                        if ($ret_email['avatar'] == $result['data']) {
//                            $msg = "信息未更改！";
//                            exit(parent::showJson("", 404, $msg));
//                        } else {
//                            $ret = db('users')->where(array('uid' => $uid))->update($data2);
//                            $up_account_data['uid'] = $uid;
//                            $up_account_data['dateline'] = time();
//                            $up_account_data['click_type'] = $click_type;
//                            $ret = db('account_info_update')->insert($up_account_data);
//                            $msg = "修改成功！";
//                            exit(parent::showJson($ret, 200, $msg));
//                        }
//                    } else {
//                        $msg = "所填信息不合法！请再次填写！";
//                        exit(parent::showJson("", 404, $msg));
//                    }
//                } else {
//                    foreach ($ret_uid_info as $v) {
//                        if ($v['uid'] == $uid && $v['click_type'] == $click_type) {
//                            $tmptime = time() - $v['dateline'] > 30 * 24 * 60 * 60;
//                            //如果是超过 30天 可以修改
//                            if ($tmptime) {
//                                $ret = db('users')->where(array('uid' => $uid))->update($data2);
//                                $up_account_data['dateline'] = time();
//                                $up_account_data['click_type'] = $click_type;
//                                $ret = db('account_info_update')->where(array('uid' => $uid))->update($up_account_data);
//                                $msg = "间隔30天修改成功！";
//                                exit(parent::showJson($ret, 200, $msg));
//                            } else {
//                                //不能修改
//                                $msg = "30天內只能修改一次！";
//                                exit(parent::showJson("", 504, $msg));
//
//                            }
//
//                        }
//                    }
//                }
//            } else {
//                $result['data'] = '/uploads/' . $path;
//            }
//            return $result;
//        } else {
//            // 上传失败获取错误信息
////            $result['code'] = 0;
////            $result['msg'] = '图片上传失败!';
////            $result['data'] = '';
////            return $result;
//            $msg = "图片上传失败！";
//            exit(parent::showJson("", 0, $msg));
//        }
//    }

    /**
     * 更新 密码
     * 2018年4月3日 by wb
     *
     */
    public function submitFixPwdbak()
    {
        $data = input();
        $uid = session('user.uid');
        $oldpw = md5($data['oldpw']);
        $password = db('users')->field('password')->where(array('uid' => $uid))->find();
        if ($oldpw == $password['password']) {
            //新密码
            $up_data['password'] = md5($data['newpw2']);
            $ret = db('users')->where(array('uid' => $uid))->update($up_data);
            $msg = "修改密码成功！";
            exit(parent::showJson($ret, 200, $msg));
        } else {
            $msg = "旧密码错误！";
            exit(parent::showJson("UPDATE_ERROR", 404, $msg));
        }
    }

    public function submitFixPwd()
    {
        $data = input();
        $uid = session('user.uid');

        if($data['oldpw']==""||$data['newpw']==""||$data['newpw2']==""){
            $msg = "所填信息不能为空！";
            exit(parent::showJson("NULL_ERROR", 405, $msg));
        }
        $click_type = input('click_type');
        $oldpw = md5($data['oldpw']);
        if ($click_type == "submitFixPwd") {
            $click_type = 5;
        }
        $ret_uid_info = db('account_info_update')->where(array('uid' => $uid,'click_type'=>$click_type))->select();
        $up_data['password'] = md5($data['newpw2']);
        if (empty($ret_uid_info)) {
            if (!empty($data['oldpw'])&&!empty($data['newpw'])&&!empty($data['newpw'])) {
                $password = db('users')->field('password')->where(array('uid' => $uid))->find();
                if($data['newpw']!=$data['newpw2']){
                    $msg = "两次密码不一致！";
                    exit(parent::showJson("PWD_CHECK_ERROR", 405, $msg));
                }
                if ($oldpw == $password['password']) {
                    //新密码
                    $ret = db('users')->where(array('uid' => $uid))->update($up_data);
                    $up_account_data['uid'] = $uid;
                    $up_account_data['dateline'] = time();
                    $up_account_data['click_type'] = $click_type;
                    $ret = db('account_info_update')->insert($up_account_data);
                    $msg = "修改密码成功！";
                    exit(parent::showJson($ret, 200, $msg));
                }else {
                    $msg = "旧密码错误！";
                    exit(parent::showJson("UPDATE_ERROR", 404, $msg));
                }
            }
        } else {
            foreach ($ret_uid_info as $v) {
                if ($v['uid'] == $uid && $v['click_type'] == $click_type) {
                    $tmptime = time() - $v['dateline'] > 30 * 24 * 60 * 60;
                    //如果是超过 30天 可以修改
                    if ($tmptime) {
                        $ret = db('users')->where(array('uid' => $uid))->update($up_data);
                        $up_account_data['dateline'] = time();
                        $up_account_data['click_type'] = $click_type;
                        $ret = db('account_info_update')->where(array('uid' => $uid))->update($up_account_data);
                        $msg = "间隔30天修改成功！";
                        exit(parent::showJson($ret, 200, $msg));
                    } else {
                        //不能修改
                        $msg = "30天內只能修改一次！";
                        exit(parent::showJson("LIMINT_ERROR", 504, $msg));

                    }

                }
            }
        }
    }


        /**
     * 修改邮箱
     * 2018年4月3日 by wb
     *
     */
    public function fixEmailbak()
    {
        $fixEmail = input('fixEmail');
        $tmp = !empty($fixEmail);
        if ($tmp) {
            $uid = session('user.uid');
            $up_data['email'] = $fixEmail;
            $ret_email = db('users')->field('email')->where(array('uid' => $uid))->find();
            if ($ret_email['email'] == $fixEmail) {
                $msg = "信息未更改！";
                exit(parent::showJson("UPDATE_ERROR", 404, $msg));
            } else {
                $ret = db('users')->where(array('uid' => $uid))->update($up_data);
                $msg = "修改成功！";
                exit(parent::showJson($ret, 200, $msg));
            }
        } else {
            $msg = "所填信息不合法！请再次填写！";
            exit(parent::showJson("INTRODUCE_ERROR", 404, $msg));
        }
    }

    public function fixEmail()
    {
        $uid = session('user.uid');
        $fixEmail = input('fixEmail');
        if(empty($fixEmail)){
            $msg = "所填信息不能为空！";
            exit(parent::showJson("NULL_ERROR", 405, $msg));
        }
        $click_type = input('click_type');
        $up_data['email'] = $fixEmail;
        if ($click_type == "fixEmail") {
            $click_type = 5;
        }
        $ret_uid_info = db('account_info_update')->where(array('uid' => $uid,'click_type'=>$click_type))->select();
        if (empty($ret_uid_info)) {
            $tmp = !empty($fixEmail);
            if ($tmp) {
                $ret_email = db('users')->field('email')->where(array('uid' => $uid))->find();
                if ($ret_email['email'] == $fixEmail) {
                    $msg = "信息未更改！";
                    exit(parent::showJson("UPDATE_ERROR", 404, $msg));
                } else {
                    $ret = db('users')->where(array('uid' => $uid))->update($up_data);
                    $up_account_data['uid'] = $uid;
                    $up_account_data['dateline'] = time();
                    $up_account_data['click_type'] = $click_type;
                    $ret = db('account_info_update')->insert($up_account_data);
                    $msg = "修改成功！";
                    exit(parent::showJson($ret, 200, $msg));
                }
            } else {
                $msg = "所填信息不合法！请再次填写！";
                exit(parent::showJson("INTRODUCE_ERROR", 404, $msg));
            }
        } else {
            foreach ($ret_uid_info as $v) {
                if ($v['uid'] == $uid && $v['click_type'] == $click_type) {
                    $tmptime = time() - $v['dateline'] > 30 * 24 * 60 * 60;
                    //如果是超过 30天 可以修改
                    if ($tmptime) {
                        $ret = db('users')->where(array('uid' => $uid))->update($up_data);
                        $up_account_data['dateline'] = time();
                        $up_account_data['click_type'] = $click_type;
                        $ret = db('account_info_update')->where(array('uid' => $uid))->update($up_account_data);
                        $msg = "间隔30天修改成功！";
                        exit(parent::showJson($ret, 200, $msg));
                    } else {
                        //不能修改
                        $msg = "30天內只能修改一次！";
                        exit(parent::showJson("LIMINT_ERROR", 504, $msg));

                    }

                }
            }
        }
    }

}
