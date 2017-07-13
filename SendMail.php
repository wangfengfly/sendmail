<?php

/**
 * Created by PhpStorm.
 * User: wangfeng211731
 * Date: 2017/7/12
 * Time: 18:36
 */

class MailFactory{
    private static $_instance = null;

    private function __construct(){
    }

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new MailFactory();
        }
        return self::$_instance;
    }
    /*
     * 防止克隆
     */
    public function __clone(){
        die('not allowed to clone ' . E_USER_ERROR);
    }

    public function create($type=''){
        $class = $type.'SendMail';
        return new $class;
    }
}

class Mail{
    protected $to;
    protected $subject;
    protected $body;
    protected $from;
    protected $cc;
    protected $bcc;

    public function __construct(){
        $this->checkSendmail();
    }
    //检查sendmail服务有没有启动
    public function checkSendmail(){
        exec("pgrep sendmail", $pids);
        if($pids==null || count($pids)==0){
            echo "your sendmail is not running, if not installed, please install and startup sendmail\n";
            echo "install command: yum install sendmail\n";
            echo "start command: service sendmail start\n";
            exit;
        }
    }
}

class SendMail extends Mail{

    public function init($to, $subject, $body, $from='', $cc='', $bcc=''){
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        $this->from = $from;
        $this->cc = $cc;
        $this->bcc = $bcc;
        return $this;
    }

    /*
     * $to 邮件接收地址,多个地址用英文逗号分隔
     * $body 邮件体
     * $subject 邮件标题
     * $from 邮件发起人
     * $cc 抄送地址
     * $bcc 密送地址，其他收件人看不到该地址
     */
    public function send(){
        $str = "echo '$this->body' | mail -s '$this->subject'";
        if($this->cc!=''){
            $str .= " -c $this->cc";
        }
        if($this->bcc!=''){
            $str .= " -b $this->bcc";
        }
        if($this->from!=''){
            $str .= " -S from=$this->from";
        }
        $str .= " $this->to";
        $res = shell_exec($str);
        if(!$res) {
            return true;
        }
        return false;
    }

    public function  sendAttachment($filename){
        $str = "rpm -qa | grep mutt";
        $res = shell_exec($str);
        if(!$res){
            echo "your mutt is not installed, please install it with command 'yum install mutt'\n";
            exit;
        }
        $str = "echo '$this->body' | mutt -s '$this->subject' -a $filename -- $this->to ";
        $res = shell_exec($str);
        if(!$res){
            return false;
        }
        return true;
    }

}

$mail = MailFactory::getInstance()->create();
$s = $mail->init('wangfeng211731@hello.com', '早上好', '你好',
    '985004673@qq.com')->send();
$s = $mail->init('985004673@qq.com', '有附件的邮件', '早上好',
    'wangfeng211731@hello.com')->sendAttachment('/home/wangfeng/test.png');

