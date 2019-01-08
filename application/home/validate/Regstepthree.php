<?php
namespace app\home\validate;
use think\Validate;

class Regstepthree extends Validate{
    protected $regex = [ 'mobile' => '^1[3|4|5|7|8][0-9]\d{4,8}$',
        'id_card'=>"/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X|x)$/",
        'email'=>"/^(([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5}){1,25})$/",
];

    /**
    [nickname] =>
    [account_introduce] =>
    [media_area] =>
    [choose_media_area] =>
    [avatar] =>
    [real_name] =>
    [id_card] =>
    [choose_id_card] =>
    [handheld_certificate] =>
    [obverse_side] =>
    [reverse_side] =>
    [city] =>
    [province] =>
    [email] =>
    [auxiliary_materials] =>
    [professional_qualification] =>
    [wechat] =>
    [qq] =>
     */
    protected $rule=[
//        'mobile'  =>  'require|regex:mobile|unique:users',
        'mobile'  =>  'require|regex:mobile',
        'nickname' => 'require',
        'account_introduce'=>'require',
        'media_area'=>'require',
        'avatar'=>'require',
        'real_name'=>'require',
        'id_card'=>'require|regex:id_card',
        'handheld_certificate'=>'require',
        'obverse_side'=>'require',
        'reverse_side'=>'require',
        'province'=>'require',
        'city'=>'require',
        'email'=>'require|regex:email',
//        'name'=>'require'
    //注册第三步

   ];
    protected $message = [
        'mobile.require'  =>  '请输入手机号码!',
        'mobile.regex'=>   '请输入正确的手机号码!',
        'mobile.unique'=>   '手机号码不能重复!',
        'nickname.require' =>'昵称不能为空!',
        'introduce.require' =>'账号介绍不能为空!',
        'media_area.require' =>'媒体领域不能为空!',
        'avatar.require' =>'头像不能为空!',
        'real_name.require' =>'真名不能为空!',
        'id_card.require' =>'身份证号不能为空!',
        'id_card.regex'=>   '请输入正确的身份证号!',
        'handheld_certificate.require' =>'手持证件照不能为空!',
        'obverse_side.require' =>'证件照正面不能为空!',
        'reverse_side.require' =>'证件照背面不能为空!',
        'province.require' =>'省份不能为空!',
        'city.require' =>'城市或区不能为空!',
        'email.require' =>'邮箱不能为空!',
        'email.regex' =>'请输入合法的邮箱!',
//        'name.require' =>'姓名不能为空!',
    ];
    // 自定义验证规则

}