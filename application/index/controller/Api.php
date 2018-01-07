<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  jswei30@gmail.com
# @Filename: Api.php  开发接口
# @Last modified by:   魏巍
# @Last modified time: 2017-11-22T16:23:55+08:00

namespace app\index\controller;

use think\Validate;
use think\Session;

class Api extends Base
{
    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 个人信息
     * @param int $id
     * @return \think\response\Json
     */
    public function personal_info($id=0)
    {
        if (!request()->isPost()) {
            return json(['status'=>0,'msg'=>'错误的请求方式']);
        }
        $_id = $id?$id:session('_mid');
        if (!$_id) {
            return json(['status'=>0,'msg'=>'缺少必要的条件']);
        }
        $userinfo = db('member')
          ->field('id,password,openid,last_login_time,last_login_address,last_login_ip,status,dates', true)
          ->find($_id);
        if (!$userinfo) {
            return json(['status'=>0,'msg'=>'查询失败']);
        }
        return json(['status'=>1,'msg'=>'查询成功','data'=>$userinfo]);
    }

    /**
     * 检昵称是否存在
     * @param string $nickname
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function check_nickname($nickname='')
    {
        if (empty($nickname)) {
            return json(['status'=>0,'msg'=>'请输入要修改的昵称']);
        }
        $member = db('member')->where(['nickname'=>$nickname])->find();
        if (empty($member)) {
            return json(['status'=>1,'msg'=>'恭喜您昵称未被注册可以使用']);
        } else {
            return json(['status'=>0,'msg'=>'抱歉昵称已被占用不可使用']);
        }
    }

    /**
     * 配置站点信息
     * @return array
     */
    public function get_site()
    {
        $data=[
            'title'=>$this->site['title'],
            'logo'=>str_replace('//', '/', $this->site['url'].$this->site['logo']),
            'keywords'=>$this->site['keywords'],
            'description'=>$this->site['description'],
            'url'=>$this->site['url'],
        ];
        return ['status'=>1,'data'=>$data];
    }

    /**
     * 获取栏目
     * @param int $id
     * @return array
     */
    public function get_column($id=0)
    {
        $data = db('column')->field('id,title,name')
          ->where(['status'=>0,'fid'=>0])
          ->order('sort asc')->select();
        if ($id==0) {
            $data[0]['active']=1;
        } else {
            foreach ($data as $k => $v) {
                if ($v['id']==$id) {
                    $data[$k]['active']=1;
                } else {
                    $data[$k]['active']=0;
                }
            }
        }
        return ['status'=>1,'data'=>$data];
    }

    /**
     * 用户登陆
     * @param string $phone
     * @param string $password
     * @param int $type
     * @return \think\response\Json|\think\response\View
     */
    public function login($phone='', $password='', $type=0)
    {
        if (!request()->isPost()) {
            return json(['status'=>0,'msg'=>'错误的请求方式']);
        }
        //逻辑判断
        if (empty($phone)) {
            return json(['status'=>0,'msg'=>'请输入您的账号']);
        }
        if (empty($password)) {
            return json(['status'=>0,'msg'=>'请输入您的密码']);
        }

        if (!$type) {     //账号登录
            $where=[
                'phone'=>$phone
            ];
            if (Validate::is($phone, 'email')) {
                $where=[
                    'email'=>$phone
                ];
            }
            $admin = db("member")
                ->field('id as user_id,phone,password,nickname,head,email,last_login_time,last_login_ip,status')
                ->where($where)->find();
            if (!$admin) {
                return json(['status'=>0,'msg'=>'您的账号输入有误']);
            }

            if ($admin['password']!=$this->get_password($password)) {
                return json(['status'=>0,'msg'=>'您的密码输入有误']);
            }
        } else {
            $flag = $this->check_verify($password, true);  //验证码验证
            if (!$flag['status']) {
                return $flag;
            }
            $where=[
                'phone'=>$phone
            ];
            $admin = db("member")
                ->field('id as user_id,phone,password,nickname,head,email,last_login_time,
                last_login_ip,status')
                ->where($where)->find();
            if (!$admin) {
                return json(['status'=>0,'msg'=>'您的账号输入有误']);
            }
        }

        if ($admin['status']==1) {
            return json(['status'=>0,'msg'=>'非法操作账号已锁定，请联系管理员解封']);
        }

        //更新登录信息
        $data=array(
            'id'=>$admin['user_id'],
            'last_login_time'=>time(),
            'last_login_ip'=>request()->ip(),
            'last_login_address'=>$this->get_location()
        );

        //更新登陆信息
        db("member")->update($data);

        //保存登录状态
        session('_mid', $admin['user_id']);
        session('_m', $admin['phone']);
        //跳转目标页
        unset($admin['password']);
        unset($admin['status']);
        return json(['status'=>1,'msg'=>'登录成功','data'=>$admin]);
    }

    /**
     * 用户注册
     * @param string $phone
     * @param string $password
     * @param string $verify
     * @return \think\response\Json|\think\response\View
     */
    public function register($phone='', $password='', $verify='')
    {
        if (!request()->isPost()) {
            return json(['status'=>0,'msg'=>'请求方式错误']);
        }
        if (empty($verify)) {
            return json(['status'=>0,'msg'=>'请输入验证码']);
        }
        if (empty($phone)) {
            return json(['status'=>0,'msg'=>'请输入手机号']);
        }
        if (empty($password)) {
            return json(['status'=>0,'msg'=>'请输入您的密码']);
        }
        $flag = $this->check_verify($verify, true);  //验证码验证
        if (!$flag['status']) {
            return $flag;
        }
        $member = [
            'phone'=>$phone,
            'password'=>$this->get_password($password),
            'tel'=>$phone,
            'date'=>time()
        ];
        $admin = db('member')->where('phone', 'eq', $phone)->find();

        if ($admin) {
            return json(['status'=>0,'msg'=>'用户已存在']);
        }
        if (!db('member')->insert($member)) {
            return json(['status'=>0,'msg'=>'注册失败']);
        }
        $id = db('member')->getLastInsID();
        //保存登录状态
        return json(['status'=>1,'msg'=>'恭喜您注册成功','last_id'=>$id]);
    }

    /**
     * 找回密码
     * @param string $phone
     * @param string $password
     * @param string $confirm_password
     * @return \think\response\Json
     */
    public function set_password($phone='', $password='', $confirm_password='')
    {
        if (!request()->isPost()) {
            return json(['status'=>0,'msg'=>'请求方式错误']);
        }
        if (empty($phone)) {
            return json(['status'=>0,'msg'=>'参数错误',]);
        }
        if (empty($password)) {
            return json(['status'=>0,'msg'=>'请输入新密码']);
        }
        if (empty($confirm_password)) {
            return ['status'=>0,'msg'=>'请输入确认密码'];
        }
        if ($confirm_password!=$password) {
            return ['status'=>0,'msg'=>'两次密码不一致'];
        }
        $member = db('member')->field('id')
            ->field('date', true)
            ->where('phone', 'eq', $phone)
            ->find();
        if (empty($member)) {
            return ['status'=>0,'msg'=>'用户名不存在'];
        }
        if (!db('member')->update([
            'id'=>$member['id'],
            'password'=>$this->get_password($password),
            'dates'=>time()
        ])) {
            return ['status'=>0,'msg'=>'修改失败请重试'];
        }
        return ['status'=>1,'msg'=>'修改成功'];
    }

    /**
     * 用户退出
     * @return array
     */
    public function logout()
    {
        Session::delete('_mid');
        Session::delete('_m');
        return array('status'=>1,'msg'=>'退出成功');
    }

    /**
     * @author 魏巍
     * @description 发送验证码邮件
     * @param string   $email       邮箱
     * @return \think\response\json 返回发送结果
     */
    public function send_email_code($email='')
    {
        if (!request()->isPost()) {
            return json(['status'=>0,'msg'=>'请求方式错误']);
        }
        if (empty($email)) {
            return ['status'=>0,'msg'=>'请填写邮箱'];
        }
        if (!Validate::is($email, 'email')) {
            return ['status'=>0,'msg'=>'抱歉邮箱格式错误'];
        }
        $_code = NoRand(0, 9, 6);
        cookie($_code.'_session_code', $_code, 60*15);
        $html = "【".$this->site['title']."】:您本次的验证码:".
            $_code.",有效时间为15分钟.如果您没有使用【".$this->site['title']
            ."】相关产品,请自动忽略此邮件谢谢:)";

        if (!think_send_mail($email, $email, "【".$this->site['title']."】", $html)) {
            return ['status'=>0,'msg'=>'验证码发送失败,请稍后重试:('];
        }
        return ['status'=>1,'msg'=>'验证码发送成功到邮箱['.$email.']中,请及时查收:)'];
    }
    /**
     * 发送验证码
     * @param string $tel       手机号
     * @param int $type         类型:0通用,1注册,2重置密码
     * @return \think\response\json
     */
    public function send_message($tel='', $type=0)
    {
        if (!request()->isPost()) {
            return ['status'=>0,'msg'=>'请求方式错误'];
        }
        if (!$tel) {
            return ['status'=>0,'msg'=>'请输入发送手机号'];
        }
        if (!Validate::is($tel, '/^1[34578]\d{9}$/')) {
            return ['status'=>0,'msg'=>'请输入正确的手机号'];
        }

        if ($type==0) {
            $arr = send_sms($tel, '227478');
        } elseif ($type==1) {
            $arr = send_sms($tel, '227475');
        } else {
            $arr = send_sms($tel, '227477');
        }

        if (substr($arr, 21, 6) == 000000) {
            return ['status'=>1,'msg'=>'验证成功下发,请注意查收'];
        } else {
            return ['status'=>0,'msg'=>'验证下发失败'];
        }
    }

    /**
     *
     * @param int $uid
     * @param string $info
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function set_information($uid=0,$info=''){
        if (!request()->isPost()) {
            return ['status'=>0,'msg'=>'请求方式错误'];
        }
        if (!$uid) {
            return ['status'=>0,'msg'=>'缺少必要参数uid'];
        }
        if (!$info) {
            return ['status'=>0,'msg'=>'缺少必要参数info'];
        }
        if(mb_strlen($info)>50){
            return ['status'=>0,'msg'=>'个性签名在50个字符之内'];
        }
        if(!db('member')->update([
            'id'=>$uid,
            'information'=>$info,
            'dates'=>time()
        ])){
            return ['status'=>0,'msg'=>'个性签名设置失败'];
        }
        return ['status'=>1,'msg'=>'个性签名设置成功'];
    }

    /**
     * [set_hobbise 设置个人喜好]
     * @param integer $id      [description]
     * @param string  $hobbies [description]
     */
    public function set_hobbise($id=0, $hobbies='')
    {
        if (!request()->isPost()) {
            return ['status'=>0,'msg'=>'请求方式错误'];
        }
        if (!$id) {
            return ['status'=>0,'msg'=>'缺少必要参数id'];
        }
        if (!$hobbies) {
            return ['status'=>0,'msg'=>'缺少必要参数hobbies'];
        }
        if (is_array($hobbies)) {
            $hobbies = implode(',', $hobbies);
        }
        $member = db('member')->field('id')->find($id);
        if (!$member) {
            return ['status'=>0,'msg'=>'用户不已存在'];
        }
        if (!db('member')->update([
            'id'=>$member['id'],
            'hobbise'=>$hobbies,
            'dates'=>time()
        ])) {
            return ['status'=>0,'msg'=>'用户喜好设置失败'];
        }
        return ['status'=>1,'msg'=>'用户喜好设置成功'];
    }
    /**
     * 验证验证码
     * @param string $verify
     * @return array
     */
    public function check_code($verify='')
    {
        if (!$verify) {
            return ['status'=>0,'msg'=>'请输入验证码'];
        }
        $_result = $this->check_verify($verify);
        return $_result;
    }

    /**
     * 修改用户昵称
     * @param int $uid
     * @param string $nickname
     * @return array
     */
    public function upgrade_nickname($uid=0, $nickname='')
    {
        if (!request()->isPost()) {
            return ['status'=>0,'msg'=>'错误请求方式'];
        }
        if (!$uid) {
            return ['status'=>0,'msg'=>'缺少必要参数uid'];
        }
        if (!$nickname) {
            return ['status'=>0,'msg'=>'缺少必要参数nickname'];
        }
        $member = db('member')->field('id,nickname,phone')->find($uid);
        if (!$member) {
            return ['status'=>0,'msg'=>'用户不已存在'];
        }
        $count = db('member')
            ->where('nickname', 'like', '%'.$nickname.'%')
            ->count('*');
        if ($count) {
            return ['status'=>0,'msg'=>'用户昵称已存在'];
        }
        if (has_chiness($nickname) && mb_strlen($nickname)>30) {
            return ['status'=>0,'msg'=>'用户昵称过长'];
        }
        if (!has_chiness($nickname) && mb_strlen($nickname)>10) {
            return ['status'=>0,'msg'=>'用户昵称过长'];
        }
        if (!db('member')->update([
            'id'=>$member['id'],
            'nickname'=>$nickname,
            'dates'=>time()
        ])) {
            return ['status'=>0,'msg'=>'用户昵称修改失败'];
        }
        return ['status'=>1,'msg'=>'用户昵称修改成功','nickname'=>$nickname];
    }

    /**
     * 修改性别
     * @param int $uid
     * @param int $sex
     * @return array
     */
    public function upgrade_sex($uid=0, $sex=0)
    {
        if (!request()->isPost()) {
            return ['status'=>0,'msg'=>'错误请求方式'];
        }
        if (!$uid) {
            return ['status'=>0,'msg'=>'缺少必要参数uid'];
        }
        $member = db('member')->field('id,nickname,phone')->find($uid);
        if (!$member) {
            return ['status'=>0,'msg'=>'用户不已存在'];
        }
        if (!db('member')->update([
            'id'=>$member['id'],
            'nickname'=>$sex,
            'dates'=>time()
        ])) {
            return ['status'=>0,'msg'=>'用户性别修改失败'];
        }
        return ['status'=>1,'msg'=>'用户性别修改成功'];
    }

    /**
     * 修改头像
     * @param int $uid
     * @return array
     */
    public function upgrade_head($uid=0)
    {
        //p(request());die;
        if (!request()->isPost()) {
            //return ['status'=>0,'msg'=>'错误请求方式'];
        }
        if (!$uid) {
            return ['status'=>0,'msg'=>'缺少必要参数uid'];
        }
        $member = db('member')->field('id,head,phone')->find($uid);
        if (!$member) {
            return ['status'=>0,'msg'=>'用户不已存在'];
        }

        $uploadify = new Uploadify();
        $_result = $uploadify->upload_head();
        $_path =  DS .'uploads' . DS .'head'.DS .$_result['filename'];
        if (!db('member')->update([
            'id'=>$member['id'],
            'head'=> $_path,
            'dates'=>time()
        ])) {
            return ['status'=>0,'msg'=>'用户头像修改失败'];
        }
        $full_path = $this->site['url']. $_path;
        return ['status'=>1,'msg'=>'用户性头像改成功','fullpath'=>$full_path."?_id=".time()];
    }

    /**
     * 更新手机
     * @param int $uid
     * @param string $phone
     * @param string $verify
     * @return array
     */
    public function upgrade_phone($uid=0, $phone='', $verify='')
    {
        if (!request()->isPost()) {
            return ['status'=>0,'msg'=>'错误请求方式'];
        }
        if (!$uid) {
            return ['status'=>0,'msg'=>'缺少必要参数uid'];
        }
        if (!$phone) {
            return  ['status'=>0,'msg'=>'请输入手机号'];
        }
        if (!Validate::is($phone, '/^1[34578]\d{9}$/')) {
            return ['status'=>0,'msg'=>'手机号码不正确'];
        }
        if (!$verify) {
            return  ['status'=>0,'msg'=>'请输入验证码'];
        }
        $flag = $this->check_verify($verify, true);  //验证码验证
        if (!$flag['status']) {
            return $flag;
        }
        $member = db('member')->field('id,phone')->find($uid);
        if (!$member) {
            return ['status'=>0,'msg'=>'用户不已存在'];
        }
        if ($member['phone']==$phone) {
            return ['status'=>0,'msg'=>'手机号已存在,请更换一个'];
        }

        if (!db('member')->update([
            'id'=>$member['id'],
            'phone'=>$phone,
            'dates'=>time()
        ])) {
            return ['status'=>0,'msg'=>'安全手机跟换失败'];
        }
        return ['status'=>1,'msg'=>'安全手机跟换成功'];
    }

    /**
     * 更换邮箱
     * @param int $uid
     * @param string $email
     * @param string $verify
     * @return array
     */
    public function upgrade_email($uid=0, $email='', $verify='')
    {
        if (!request()->isPost()) {
            return ['status'=>0,'msg'=>'错误请求方式'];
        }
        if (!$uid) {
            return ['status'=>0,'msg'=>'缺少必要参数uid'];
        }
        if (!$email) {
            return ['status'=>0,'msg'=>'请输入邮箱'];
        }
        if (!Validate::is($email, 'email')) {
            return ['status'=>0,'msg'=>'邮箱格式不正确'];
        }
        if (!$verify) {
            return ['status'=>0,'msg'=>'请输入邮箱验证码'];
        }
        $flag = $this->check_verify($verify, true);  //验证码验证
        if (!$flag['status']) {
            return $flag;
        }
        $member = db('member')->field('id,email')->find($uid);
        if (!$member) {
            return ['status'=>0,'msg'=>'用户不已存在'];
        }

        if (!db('member')->update([
            'id'=>$member['id'],
            'email'=>$email,
            'dates'=>time()
        ])) {
            return ['status'=>0,'msg'=>'安全邮箱跟换失败'];
        }
        return ['status'=>1,'msg'=>'安全邮箱跟换成功'];
    }

    /**
     * 根据ip获取位置
     * @param string $ip
     * @param int $type
     * @return \think\response\Json
     */
    public function get_ip_location($ip='', $type=0)
    {
        if (!$ip) {
            return json([
                'status'=>0,
                'msg'=>'缺少必要参数IP地址'
            ]);
        }
        switch ($type) {
            case 1:
                $param = ['key'=>config('AMAP.KEY'),'ip'=>$ip];
                $location = http('http://restapi.amap.com/v3/ip', $param);
                unset($location['status']);
                unset($location['info']);
                unset($location['infocode']);
                break;
            case 0:
            default:
                $_ip = new \service\IpLocation();
                $location = $_ip->get_location($ip);
                break;
        }
        return [
            'status'=>1,
            'msg'=>'定位成功',
            'data'=>$location
        ];
    }

    /**
     * 获取省份
     * @param string $limit
     * @return \think\response\Json
     */
    public function get_province($limit='')
    {
        $list = db('provinces')
            ->field('provinceid,province')
            ->where('type', 'eq', 0)
            ->limit($limit)
            ->select();
        if (!$list) {
            return json([
                'status'=>0,
                'msg'=>'没有查到数据'
            ]);
        }

        return json([
            'status'=>1,
            'msg'=>'查询成功',
            'data'=>$list
        ]);
    }
    /**
     * 获取市区信息
     * @param string $provinceid
     * @param string $limit
     * @param string $q
     * @return \think\response\Json
     */
    public function get_city($provinceid='', $limit='', $q='')
    {
        $where=[];
        if ($provinceid) {
            $where['provinceid']=$provinceid;
        }
        if ($q) {
            $where['city']=['like','%'.$q.'%'];
        }
        $list = db('cities')
            ->field('provinceid,cityid,city')
            ->where('type', 'eq', 0)
            ->where($where)
            ->limit($limit)
            ->select();
        if (!$list) {
            return [
                'status'=>0,
                'msg'=>'没有查到数据'
            ];
        }

        return [
            'status'=>1,
            'msg'=>'查询成功',
            'data'=>$list
        ];
    }

    /**
     * 获取县区/街道
     * @param string $cityid
     * @return \think\response\Json
     */
    public function get_areas($cityid='')
    {
        if (!$cityid) {
            return json([
                'status'=>0,
                'msg'=>'缺少必要的参数cityid'
            ]);
        }
        $list = db('areas')
            ->field('areaid,area')
            ->where('cityid', 'eq', $cityid)
            ->select();
        if (!$list) {
            return [
                'status'=>0,
                'msg'=>'没有查到数据'
            ];
        }

        return [
            'status'=>1,
            'msg'=>'查询成功',
            'data'=>$list
        ];
    }
    /**
     * [push_app 推送消息APP]
     * @param  string $title  [消息头]
     * @param  string $text   [消息正文]
     * @param  string $banner [显示图片]
     * @param  string $url    [跳转地址]
     * @return [type]         [description]
     */
    public function push_app($title='', $text='', $banner='', $url='')
    {
        if (!request()->isPost()) {
            //return ['status'=>0,'msg'=>'错误请求方式'];
        }
        if (!$title) {
            return [
              'status'=>0,
              'msg'=>'缺少参数[title]'
          ];
        }
        if (!$text) {
            return [
              'status'=>0,
              'msg'=>'缺少参数[text]'
          ];
        }
        //个推类
        $getui = new \service\GeTui();
        //通知样式
        $style = new \service\MessageStyle();
        $style->type=\service\MessageType::getui;
        $style->text=$text;
        $style->title=$title;
        $style->is_ring=true;
        $style->logourl=$banner?$banner:''; //http://q1.qlogo.cn/g?b=qq&nk=524314430&s=100&t=1511320210

        if ($url) {
            //链接消息
            $link = new \service\LinkNotifi();
            $link->cid='0b056f0b02b629a0e3a809f1bf504fb2';
            $link->set_style($style);
            $link->set_url($url);
            $_data  = $link->merge();
        } else {
            $link = new \service\TextNotifi();
            $link->cid='0b056f0b02b629a0e3a809f1bf504fb2';
            $link->set_style($style);
            $_data  = $link->merge();
        }
        $result = $getui->push_app($_data);
        $_url = $url?$url:'未填写';
        $_banner=$banner?$banner:'未填写';
        $data = [
          'title'=>$title,
          'content'=>$text."|".$_banner."|".$_url,
          'date'=>time(),
          'type'=>0
        ];
        if ($result['result']=='ok') {
            $data['status']=1;
            db('message')->insert($data);
            return [
              'status'=>1,
              'msg'=>'消息推送成功'
            ];
        } else {
            $data['status']=0;
            db('message')->insert($data);
            return [
              'status'=>0,
              'msg'=>'消息推送失败'
            ];
        }
    }
    /**
     * [push_recive 修改推送]
     * @param  integer $uid    [用户ID]
     * @param  integer $recive [推送：0不接受，1接受，默认：1]
     * @return [type]          [description]
     */
    public function push_recive($uid=0, $recive=1)
    {
        if (!request()->isPost()) {
            return ['status'=>0,'msg'=>'错误请求方式'];
        }
        if (!$uid) {
            return ['status'=>0,'msg'=>'缺少必要参数uid'];
        }
        $member = db('member')->field('id')->find($uid);
        if (!$member) {
            return ['status'=>0,'msg'=>'用户不已存在'];
        }

        if (!db('member')->update([
          'id'=>$member['id'],
          'is_recive'=>$recive,
          'dates'=>time()
        ])) {
            return ['status'=>0,'msg'=>'设置失败'];
        }
        return ['status'=>1,'msg'=>'设置成功'];
    }

    /**
     * 检测验证码
     * @param string $verify
     * @param bool $clear
     * @return array
     */
    protected function check_verify($verify='', $clear=false)
    {
        $d = cookie($verify.'_session_code');
        $f = cookie('?'.$verify.'_session_code');

        if (!$f) {
            return ['status'=>0,'msg'=>'验证码已失效'];
        }
        if ($verify!=$d) {
            return ['status'=>0,'msg'=>'验证码不正确'];
        }
        if ($clear) {
            cookie($verify.'_session_code', null, time()-60*2);
        }
        return ['status'=>1,'msg'=>'验证码正确'];
    }

    /**
     * 获取密码
     * @param $pwd
     * @return bool|string
     */
    protected function get_password($pwd)
    {
        return substr(md5($pwd), 10, 15);
    }
}
