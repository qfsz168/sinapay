<?php

namespace sinapay;

/**
 * Class 新浪支付
 * 单例模式
 * @package pay
 */
class Sinapay {

    public const PARTNER_ID = SINAPAY_PARTNER_ID;//收银台模式商户号，由新浪支付提供

    protected const DEBUG = true;//调试模式

    protected const SERVICE_TYPE_MAS = 2;//订单类接口
    protected const SERVICE_TYPE_MGS = 1;//会员类接口

    protected const NOT_SIGN = [
        "sign",
        "sign_type",
        "sign_version",
    ];//不进行签名的参数
    protected const LOG_PATH = __ROOT__."runtime/sinapay_log/";//日志文件路径
    protected const MAS      = [
        "create_hosting_collect_trade"            => "创建托管代收交易",
        "create_single_hosting_pay_trade"         => "创建托管代付交易",
        "create_batch_hosting_pay_trade"          => "创建批量托管代付交易",
        "pay_hosting_trade"                       => "托管交易支付",
        "query_pay_result"                        => "支付结果查询",
        "query_hosting_trade"                     => "托管交易查询",
        "query_hosting_batch_trade"               => "托管交易批次查询",
        "create_hosting_refund"                   => "托管退款",
        "query_hosting_refund"                    => "托管退款查询",
        "create_hosting_deposit"                  => "托管充值",
        "query_hosting_deposit"                   => "托管充值查询",
        "create_hosting_withdraw"                 => "托管提现",
        "query_hosting_withdraw"                  => "托管提现查询",
        "create_single_hosting_pay_to_card_trade" => "创建单笔代付到提现卡交易",
        "create_batch_hosting_pay_to_card_trade"  => "创建批量代付到提现卡交易",
        "finish_pre_auth_trade"                   => "代收完成",
        "cancel_pre_auth_trade"                   => "代收撤销",
        "create_bid_info"                         => "标的录入",
        "query_bid_info"                          => "标的信息查询",

        "create_hosting_transfer" => "转账接口",
        "advance_hosting_pay"     => "支付推进",
    ];//订单类接口
    protected const MGS      = [
        "create_activate_member"      => "创建激活会员",
        "set_real_name"               => "设置实名信息",
        "set_pay_password"            => "设置支付密码重定向",
        "modify_pay_password"         => "修改支付密码重定向",
        "find_pay_password"           => "找回支付密码重定向",
        "query_is_set_pay_password"   => "查询是否设置支付密码",
        "binding_bank_card"           => "绑定银行卡",
        "binding_bank_card_advance"   => "绑定银行卡推进",
        "unbinding_bank_card"         => "解绑银行卡",
        "unbinding_bank_card_advance" => "解绑银行卡推进",
        "query_bank_card"             => "查询银行卡",
        "query_balance"               => "查询余额/基金份额",
        "query_account_details"       => "查询收支明细",
        "balance_freeze"              => "冻结余额",
        "balance_unfreeze"            => "解冻余额",
        "query_ctrl_result"           => "查询冻结解冻结果",
        "query_member_infos"          => "查询企业会员信息",
        "query_audit_result"          => "查询企业会员审核结果",
        "audit_member_infos"          => "请求审核企业会员资质",
        "smt_fund_agent_buy"          => "经办人信息",
        "query_fund_agent_buy"        => "查询经办人信息",
        "show_member_infos_sina"      => "sina 页面展示用户信息",
        "query_middle_account"        => "查询中间账户",
        "modify_verify_mobile"        => "修改认证手机",
        "find_verify_mobile"          => "找回认证手机",

        "binding_verify"             => "绑定认证信息",
        "unbinding_verify"           => "解绑认证信息",
        "query_verify"               => "查询认证信息",
        "web_binding_bank_card"      => "我的银行卡",
        "open_account"               => "会员开户接口",
        "handle_withhold_authority"  => "委托扣款重定向",
        "modify_withhold_authority"  => "修改委托扣款重定向",
        "relieve_withhold_authority" => "解除委托扣款重定向",
        "query_withhold_authority"   => "查看用户是否委托扣款",
        "set_merchant_config"        => "修改商户配置",
        "web_real_name_pic_auth"     => "实名图像认证重定向",
        "init_member_by_process"     => "综合初始化会员接口",
        "query_merchant_config"      => "查询商户配置",
    ];//会员类接口

    protected $_url_mgs              = SINAPAY_MGS_URL; //会员类网关地址
    protected $_url_mas              = SINAPAY_MAS_URL; //订单类网关地址
    protected $_rsa_public_key       = SINAPAY_RSA_PUBLIC_KEY; // 新浪支付特殊参数加密，公钥，由新浪支付提供
    protected $_rsa_sign_public_key  = SINAPAY_RSA_SIGN_PUBLIC_KEY; //商户验证签名公钥，由新浪提供
    protected $_rsa_sign_private_key = SINAPAY_RSA_SIGN_PRIVATE_KEY; //商户签名私钥，由商户自己生成

    protected $_sftp_host        = SINAPAY_SFTP_HOST;
    protected $_sftp_port        = SINAPAY_SFTP_PORT;
    protected $_sftp_user        = SINAPAY_SFTP_USER;//sftp用户名
    protected $_sftp_private_key = SINAPAY_SFTP_PRIVATE_KEY; //sftp登录私钥
    protected $_sftp_public_key  = SINAPAY_SFTP_PUBLIC_KEY; //sftp登录公钥

    protected $_baseParams = [
        "version"        => SINAPAY_VERSION,
        "partner_id"     => self::PARTNER_ID,
        "_input_charset" => SINAPAY_INPUT_CHARSET,
        "sign_type"      => SINAPAY_SIGN_TYPE,
        "notify_url"     => SINAPAY_NOTIFY_URL,

        "return_url"   => null,
        "service"      => null,
        "sign"         => null,
        "request_time" => null,
        "memo"         => null,
    ];//基本参数
    protected $_bizParams  = [];//业务参数
    protected $_paramsData = null;//要发送的数据

    protected $_isBaseParamed = false;//是否设置基本参数
    protected $_isBizParamed  = false;//是否设置业务参数
    protected $_isSigned      = false;//是否签名

    protected $_serviceType = null;//接口类型(1,会员类;2,订单类)


    public function __construct() {
    }


    /**
     * 基本参数
     * @author 王崇全
     * @date
     * @param string      $apiName   接口名(参见新浪文档)
     * @param string      $returnUrl 页面跳转同步返回页面路径处理完请求后， 当前页面自动跳转到商户网站里指定页面的http路径
     * @param string|null $memo      说明信息, 新浪会原文返回
     * @return Sinapay $this
     */
    public function setBase(string $apiName, string $memo = null, string $returnUrl = null) {

        $this->_baseParams["service"]    = $apiName;
        $this->_baseParams["return_url"] = $returnUrl;

        $this->_baseParams["request_time"] = date("YmdHis");

        if (is_null($memo) || $memo === "") {
            unset($this->_baseParams["memo"]);
        } else {
            $this->_baseParams["memo"] = $memo;
        }

        $this->_isBaseParamed = true;
        $this->_isSigned      = false;

        return $this;
    }

    /**
     * 业务参数
     * @author 王崇全
     * @date
     * @param array $bizParams 业务参数集[业务参数名称=>业务参数值]
     * @return Sinapay $this
     */
    public function setBiz(array $bizParams = []) {

        $this->_bizParams = $this->_bizParams + $bizParams;

        $this->_isBizParamed = true;
        $this->_isSigned     = false;

        return $this;
    }

    /**
     * 业务参数-RSA加密
     * @author 王崇全
     * @date
     * @param array $bizParamsRsa 需要RSA加密的业务参数集[参数名=>参数值]
     * @return Sinapay $this
     */
    public function setBizRsa(array $bizParamsRsa = []) {

        foreach ($bizParamsRsa as &$v) {
            if (isset ($v) && !is_null($v) && @$v != "") {
                $v = $this->rsaEncrypt($v);
            }
        }

        $this->_bizParams = $this->_bizParams + $bizParamsRsa;

        $this->_isBizParamed = true;
        $this->_isSigned     = false;

        return $this;
    }

    /**
     * 业务参数-文件摘要
     * @author 王崇全
     * @date
     * @param string $paramName 业务参数名称
     * @param string $file      文件
     * @return $this
     * @throws \Exception
     */
    public function setBizFileMd5(string $paramName, string $file) {

        if (!is_file($file)) {
            throw new \Exception("{$file}不是有效文件");
        }

        $this->_bizParams[ $paramName ] = $this->md5File($file);

        $this->_isBizParamed = true;
        $this->_isSigned     = false;

        return $this;
    }

    /**
     * 业务参数-ip
     * @author 王崇全
     * @date
     * @param string $paramName 业务参数名称
     * @return $this
     * @throws \Exception
     */
    public function setBizIp(string $paramName) {

        $this->_bizParams[ $paramName ] = $this->getIp();

        $this->_isBizParamed = true;
        $this->_isSigned     = false;

        return $this;
    }

    /**
     * 发送请求并获取返回值
     * @author 王崇全
     * @date
     * @param bool $getAll 是否获取基本响应信息
     * @return array 新浪的返回值
     * @throws \Exception
     */
    public function send(bool $getAll = false) {

        if (!($this->_isBaseParamed && $this->_isBizParamed)) {
            throw new \Exception("请先设置基本参数和业务参数");
        }

        //参数签名
        $this->getSignMsg();

        if (!$this->_isSigned) {
            throw new \Exception("请先参数签名");
        }

        //拼接参数
        $this->createParamsMsg();

        //发送请求
        $res = $this->curlPost();

        $this->resetParams();

        //获取并验证返回值
        return $this->checkSignMsg($res, $getAll);
    }

    /**
     * 异步回调签名校验
     * @author 王崇全
     * @date
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function checkNotifySign(array $data) {

        //对返回数据进行排序
        ksort($data);

        $paramsStr = "";
        foreach ($data as $key => $val) {
            if (!in_array($key, self::NOT_SIGN) && !is_null($val) && @$val != "") {
                $paramsStr .= "&".trim($key)."=".trim($val);
            }
        }

        if ($paramsStr) {
            $paramsStr = substr($paramsStr, 1);
        }

        $cert     = file_get_contents($this->_rsa_sign_public_key);
        $pubkeyid = openssl_pkey_get_public($cert);

        $res = openssl_verify($paramsStr, base64_decode($data ['sign']), $cert);
        openssl_free_key($pubkeyid);

        if ($res !== 1) {
            throw new \Exception("签名校验失败");
        }
    }


    /**
     * log
     * @author 日志记录
     * @date
     * @param string $msg
     * @return bool
     * @throws \Exception
     */
    public function log(string $msg) {

        if (!self::DEBUG) {
            return false;
        }

        $path = self::LOG_PATH;
        if (!is_dir($path)) {
            if (!mkdir($path, 0777)) {
                throw new \Exception("文件夹创建失败".$path);
            }
        }


        return error_log(date("[H:i:s]").": ".$msg."\r\n", 3, self::LOG_PATH.date("Ymd").'.log');
    }


    /**
     * sftp上传
     * @author 王崇全
     * @date
     * @param string $file     本地文件
     * @param string $filename 远程文件名
     * @return bool
     */
    public function sftpUpload(string $file, string $filename) {

        $this->log("sftp连接地址:".$this->_sftp_host);
        $this->log("sftp连接端口:".$this->_sftp_port);
        $this->log("sftp连接用户名:".$this->_sftp_user);
        $this->log("sftp连接公钥:".$this->_sftp_public_key);
        $this->log("sftp连接私钥:".$this->_sftp_private_key);
        $this->log("ssh2_connect status:".print_r(get_extension_funcs("ssh2_connect")));

        $resConnection = ssh2_connect($this->_sftp_host, $this->_sftp_port);

        $res = ssh2_auth_pubkey_file($resConnection, $this->_sftp_user, $this->_sftp_public_key, $this->_sftp_private_key);
        if (!$res) {
            return false;
        }

        $resSFTP = ssh2_sftp($resConnection);

        if (!copy($file, "ssh2.sftp://{$resSFTP}/upload/$filename")) {
            return false;
        }

        return true;
    }

    /**
     * sftp下载
     * @author 王崇全
     * @date
     * @param string $path     下载文件路径
     * @param string $filename 下载文件名称
     * @return bool
     */
    public function sftpDownload(string $path, string $filename) {

        $connection = ssh2_connect($this->_sftp_host, $this->_sftp_port);

        if (!ssh2_auth_pubkey_file($connection, $this->_sftp_user, $this->_sftp_public_key, $this->_sftp_private_key)) {
            return false;
        }

        $start_time = microtime(true);

        $resSFTP = ssh2_sftp($connection);
        $opts    = [
            'http' => [
                'method'  => "GET",
                'timeout' => 60,
            ],
        ];
        $context = stream_context_create($opts);
        $strData = file_get_contents("ssh2.sftp://{$resSFTP}/upload/busiexport/$filename", false, $context);

        $end_time = microtime(true);//获取程序执行结束的时间
        $total    = $end_time - $start_time; //计算差值

        if (!file_put_contents($path.$filename, $strData)) {

            $this->log($filename."下载失败，耗时".$total."秒");

            return false;
        }

        $this->log($filename."下载成功，耗时".$total."秒");

        return true;
    }


    /**
     * 计算签名
     * @author 王崇全
     * @date
     * @return void
     */
    protected function getSignMsg() {

        unset($this->_baseParams["sign"]);

        $this->_paramsData = $this->_baseParams + $this->_bizParams;

        ksort($this->_paramsData);

        $toSigndParams = "";
        foreach ($this->_paramsData as $key => $val) {

            if (!isset ($val) || @$val === "") {
                continue;
            }

            if (in_array($key, self::NOT_SIGN)) {
                continue;
            }

            $toSigndParams .= "&".trim($key)."=".trim($val);
        }
        $toSigndParams = substr($toSigndParams, 1);

        $this->log("RSA参与签名运算数据: ".$toSigndParams);

        $privKey = file_get_contents($this->_rsa_sign_private_key);
        $pkeyid  = openssl_pkey_get_private($privKey);

        $signMsg = "";
        openssl_sign($toSigndParams, $signMsg, $pkeyid, OPENSSL_ALGO_SHA1);

        openssl_free_key($pkeyid);

        $signMsg = base64_encode($signMsg);

        $this->log("RSA计算得出签名值：".$signMsg);

        $this->_baseParams["sign"] = $signMsg;
        $this->_isSigned           = true;
    }

    /**
     * 创建参数字符串
     * @author 王崇全
     * @date
     * @return void
     */
    protected function createParamsMsg() {
        $this->_paramsData["sign"] = $this->_baseParams["sign"];
        $paramsMsg                 = "";
        $paramsMsgLog              = "";
        foreach ($this->_paramsData as $key => $val) {
            if (isset ($val) && !is_null($val) && @$val != "") {
                $paramsMsg .= "&".trim($key)."=".urlencode(urlencode(trim($val)));

                $paramsMsgLog .= "&".trim($key)."=".trim($val);
            }
        }

        if (!$paramsMsg) {
            $this->_paramsData = $paramsMsg;
        } else {
            $this->_paramsData = substr($paramsMsg, 1);

            $this->log("请求sina网关数据(url编码前):".substr($paramsMsgLog, 1));
        }

    }

    /**
     * 模拟POST提交
     * @author 王崇全
     * @date
     * @return mixed|string 新浪返回值
     * @throws \Exception
     */
    protected function curlPost() {

        if (array_key_exists($this->_baseParams["service"], self::MAS)) {
            $url                = $this->_url_mas;
            $this->_serviceType = self::SERVICE_TYPE_MAS;
        } elseif (array_key_exists($this->_baseParams["service"], self::MGS)) {
            $url                = $this->_url_mgs;
            $this->_serviceType = self::SERVICE_TYPE_MGS;
        } else {
            throw new \Exception("无此接口服务");
        }

        $this->log("请求sina网关数据:".$this->_paramsData);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_paramsData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);//单位S 秒

        $res  = curl_exec($ch);
        $info = curl_getinfo($ch);

        if (curl_error($ch)) {
            $this->log("curl_http_error:".curl_error($ch));
        }

        curl_close($ch);

        $res = urldecode($res);

        $this->log("curl_http_code:".json_encode($info));
        $this->log("请求新浪网关返回内容:".$res);

        return $res;
    }

    /**
     * 返回值签名验证
     * @author 王崇全
     * @date
     * @param string $resData 参与签名验证的数据
     * @param bool   $getAll  是否获取基本响应信息
     * @return array
     * @throws \Exception
     */
    protected function checkSignMsg(string $resData, bool $getAll = false) {

        $resData = json_decode($resData, true);

        //对返回数据进行排序
        ksort($resData);

        $paramsStr = "";
        foreach ($resData as $key => $val) {
            if (!in_array($key, self::NOT_SIGN) && !is_null($val) && @$val != "") {
                $paramsStr .= "&".trim($key)."=".trim($val);
            }
        }

        if ($paramsStr) {
            $paramsStr = substr($paramsStr, 1);
        }

        if ($this->_serviceType == self::SERVICE_TYPE_MGS || $resData["response_code"] == "SUCCESS") {

            $this->log("本地验证签名数据: ".$paramsStr);
            $this->log("本地获取签名: ".$resData ['sign'] ?? []);

            $cert     = file_get_contents($this->_rsa_sign_public_key);
            $pubkeyid = openssl_pkey_get_public($cert);

            $res = openssl_verify($paramsStr, base64_decode($resData ['sign']), $cert);
            openssl_free_key($pubkeyid);

            if ($res !== 1) {
                throw new \Exception("签名校验失败");
            }
        }

        if (!$getAll) {
            unset($resData["response_time"]);
            unset($resData["_input_charset"]);
            unset($resData["sign"]);
            unset($resData["sign_type"]);
            if (isset($resData["sign_version"])) {
                unset($resData["sign_version"]);
            }
        }

        $this->log("\r\n\r\n");

        return $resData;
    }

    /**
     * 通过公钥进行rsa加密
     * @author 王崇全
     * @date
     * @param string $data 进行rsa公钥加密的数
     * @return string 加密好的密文
     */
    protected function rsaEncrypt(string $data) {
        $encrypted = "";

        $cert = file_get_contents($this->_rsa_public_key);

        // 这个函数可用来判断公钥是否是可用
        $pu_key = openssl_pkey_get_public($cert);

        // 公钥加密
        openssl_public_encrypt(trim($data), $encrypted, $pu_key);

        // 进行编码
        return base64_encode($encrypted);
    }

    /**
     * 文件摘要算法
     * @author 王崇全
     * @date
     * @param string $file 文件
     * @return string
     */
    protected function md5File(string $file) {
        return md5_file($file);
    }

    /**
     * 获取客户端IP
     * @author 王崇全
     * @date
     * @return string
     */
    protected function getIp() {

        if (isset($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], "unknown")) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], "unknown")) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "";
        }

        return $ip;
    }

    protected function resetParams() {
        $this->_baseParams = [
            "version"        => SINAPAY_VERSION,
            "partner_id"     => SINAPAY_PARTNER_ID,
            "_input_charset" => SINAPAY_INPUT_CHARSET,
            "sign_type"      => SINAPAY_SIGN_TYPE,
            "notify_url"     => SINAPAY_NOTIFY_URL,

            "return_url"   => null,
            "service"      => null,
            "sign"         => null,
            "request_time" => null,
            "memo"         => null,
        ];//基本参数
        $this->_bizParams  = [];//业务参数
        $this->_paramsData = null;//业务参数

        $this->_isBaseParamed = false;//是否设置基本参数
        $this->_isBizParamed  = false;//是否设置业务参数
        $this->_isSigned      = false;//是否签名
        $this->_serviceType   = null;//接口类型(1,会员类;2,订单类)

    }

}
