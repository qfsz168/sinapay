<?php

namespace app\pay\model;

use sinapay\Sinapay;
use think\Exception;

class Sina {

    //会员类型
    public const MEMBER_TYPE_PERSON     = 1;//个人
    public const MEMBER_TYPE_ENTERPRISE = 2;//企业

    //ID的类型
    public const IDENTITY_TYPE_UID       = "UID"; //用户ID
    public const IDENTITY_TYPE_MEMBER_ID = "MEMBER_ID"; //用户ID

    //认证类型
    public const VERIFY_TYPE_MOBILE = "MOBILE"; //手机号
    public const VERIFY_TYPE_EMAIL  = "EMAIL"; //邮箱

    //证件类型
    public const CERT_TYPE_IC = "IC"; //身份证

    //卡类型
    public const CARD_TYPE_DEBIT  = "DEBIT"; //借记卡
    public const CARD_TYPE_CREDIT = "CREDIT"; //借贷(信用卡)

    //卡属性
    public const CARD_ATTR_DEBIT  = "C"; //对私
    public const CARD_ATTR_CREDIT = "B"; //对公

    //认证方式
    public const VERIFY_MODE_SIGN = "SIGN"; //签约认证

    //存钱罐交易类型
    public const SAVING_POT_TYPE_IN    = "IN"; //申购
    public const SAVING_POT_TYPE_OUT   = "OUT"; //赎回
    public const SAVING_POT_TYPE_BONUS = "BONUS"; //收益

    //账户类型
    public const ACCOUNT_TYPE_BASIC      = "BASIC";//基本户
    public const ACCOUNT_TYPE_RESERVE    = "RESERVE";//准备金
    public const ACCOUNT_TYPE_ENSURE     = "ENSURE";//保证金户
    public const ACCOUNT_TYPE_SAVING_POT = "SAVING_POT";//存钱罐
    public const ACCOUNT_TYPE_BANK       = "BANK";//银行账户

    //银行代码
    public const BANK_CODE = [
        "ABC"      => "农业银行",
        "GNXS"     => "广州市农信社",
        "BCCB"     => "北京银行",
        "GZCB"     => "广州市商业银行",
        "BJRCB"    => "北京农商行",
        "HCCB"     => "杭州银行",
        "BOC"      => "中国银行",
        "HKBCHINA" => "汉口银行",
        "BOS"      => "上海银行",
        "HSBANK"   => "徽商银行",
        "CBHB"     => "渤海银行",
        "HXB"      => "华夏银行",
        "CCB"      => "建设银行",
        "ICBC"     => "工商银行",
        "CCQTGB"   => "重庆三峡银行",
        "NBCB"     => "宁波银行",
        "CEB"      => "光大银行",
        "NJCB"     => "南京银行",
        "CIB"      => "兴业银行",
        "PSBC"     => "中国邮储银行",
        "CITIC"    => "中信银行",
        "SHRCB"    => "上海农村商业银行",
        "CMB"      => "招商银行",
        "SNXS"     => "深圳农村商业银行",
        "CMBC"     => "民生银行",
        "SPDB"     => "浦东发展银行",
        "COMM"     => "交通银行",
        "SXJS"     => "晋城市商业银行",
        "CSCB"     => "长沙银行",
        "SZPAB"    => "平安银行",
        "CZB"      => "浙商银行",
        "UPOP"     => "银联在线支付",
        "CZCB"     => "浙江稠州商业银行",
        "WZCB"     => "温州市商业银行",
        "GDB"      => "广东发展银行",
    ];

    //外部业务代码
    public const OUT_TRAD_CODE = [
        "1000" => "代收-其它",
        "1001" => "代收投资金",
        "1002" => "代收还款金",
        "2000" => "代付-其他",
        "2001" => "代付借款金",
        "2002" => "代付（本金/收益）金",
    ];

    protected $_sp = null; //新浪支付接口类实例

    public function __construct() {
        $this->_sp = new Sinapay();
    }


    /**
     * 创建激活会员
     * @author 王崇全
     * @date
     * @param int         $userId     用户ID
     * @param int         $memberType 会员类型
     * @param string|null $memo       附加信息
     * @return array 成功,返回新浪返回的数据
     * @throws Exception 失败, 抛出异常
     */
    public function createActivateMember(int $userId, int $memberType = self::MEMBER_TYPE_PERSON, string $memo = null) {

        $res = $this->_sp->setBase("create_activate_member", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "member_type"   => $memberType,
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res;
    }

    /**
     * 设置实名信息
     * @author 王崇全
     * @date
     * @param int         $userId   用户ID
     * @param string      $realName 真实姓名
     * @param string      $certNo   身份证号
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function setRealName(int $userId, string $realName, string $certNo, string $memo = null) {

        $res = $this->_sp->setBase("set_real_name", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "cert_type"     => self::CERT_TYPE_IC,
            ])
            ->setBizRsa([
                "real_name" => $realName,
                "cert_no"   => mb_strtoupper($certNo),
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res;
    }

    /**
     * 设置支付密码-重定向
     * @author 王崇全
     * @date
     * @param int         $userId    用户ID
     * @param string      $returnUrl 页面跳转地址
     * @param string|null $memo
     * @return string  重定向URL
     * @throws Exception
     */
    public function setPayPassword(int $userId, string $returnUrl, string $memo = null) {

        $res = $this->_sp->setBase("set_pay_password", $memo, $returnUrl)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res["redirect_url"];
    }

    /**
     * 修改支付密码-重定向
     * @author 王崇全
     * @date
     * @param int         $userId    用户ID
     * @param string      $returnUrl 页面跳转地址
     * @param string|null $memo
     * @return string  重定向URL
     * @throws Exception
     */
    public function modifyPayPassword(int $userId, string $returnUrl, string $memo = null) {

        $res = $this->_sp->setBase("modify_pay_password", $memo, $returnUrl)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res["redirect_url"];
    }

    /**
     * 找回支付密码-重定向
     * @author 王崇全
     * @date
     * @param int         $userId    用户ID
     * @param string      $returnUrl 页面跳转地址
     * @param string|null $memo
     * @return string  重定向URL
     * @throws Exception
     */
    public function findPayPassword(int $userId, string $returnUrl, string $memo = null) {

        $res = $this->_sp->setBase("find_pay_password", $memo, $returnUrl)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res["redirect_url"];
    }

    /**
     * 查询是否设置支付密码
     * @author 王崇全
     * @date
     * @param int         $userId 用户ID
     * @param string|null $memo
     * @return bool
     * @throws Exception
     */
    public function queryIsSetPayPassword(int $userId, string $memo = null) {

        $res = $this->_sp->setBase("query_is_set_pay_password", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res["is_set_paypass"] === "Y" ? true : false;
    }

    /**
     * 绑定银行卡
     * 新浪支付会发送验证码短信,供推进使用
     * @author 王崇全
     * @date
     * @param int         $userId            用户ID
     * @param string      $bankCode          银行代码
     * @param string      $bankAccountNo     卡号
     * @param string      $accountName       户名
     * @param string      $phoneNo           预留手机号
     * @param string      $province          省份(如: 山东省, 上海市)
     * @param string      $city              城市(如: 济南市, 南京市)
     * @param string      $cardAttr          卡属性(默认,对私)
     * @param string      $cardType          卡类型 (默认,借记卡)
     * @param string|null $bankBranch        支行名称(为了保证出款的成功率，城市商业银行还需要输入开户分支行)
     * @param string|null $validityPeriod    信用卡专用，有效期(10/13)，（月份/年份）
     * @param string|null $verificationValue CVV2  信用卡安全码(签名栏上,紧跟卡号末位的3位数字)
     * @param string|null $memo
     * @return array [绑卡请求号,后续推进需要的参数]
     *                                       ( 后续推进需要的参数. 支付推进时需要带上此参数，ticket有效期为15分钟，可以多次使用（最多5次）)
     * @throws Exception
     */
    public function bindingBankCard(int $userId, string $bankCode, string $bankAccountNo, string $accountName, string $phoneNo, string $province, string $city, string $cardAttr = self::CARD_ATTR_DEBIT, string $cardType = self::CARD_TYPE_DEBIT, string $bankBranch = null, string $validityPeriod = null, string $verificationValue = null, string $memo = null) {

        if ($cardType === self::CARD_TYPE_CREDIT && (!$validityPeriod || !$verificationValue)) {
            throw new Exception("信用卡的有效期和安全码不能为空");
        }

        //绑卡请求号(唯一)
        $requestNo = $this->mkUniNo();

        $res = $this->_sp->setBase("binding_bank_card", $memo)
            ->setBiz([
                "request_no"     => $requestNo,
                "identity_id"    => $userId,
                "identity_type"  => self::IDENTITY_TYPE_UID,
                "bank_code"      => $bankCode,
                "card_type"      => $cardType,
                "card_attribute" => $cardAttr,
                "province"       => $province,
                "city"           => $city,
                "verify_mode"    => self::VERIFY_MODE_SIGN,
                "bank_branch"    => $bankBranch,
            ])
            ->setBizRsa([
                "bank_account_no"    => $bankAccountNo,
                "account_name"       => $accountName,
                "phone_no"           => $phoneNo,
                "validity_period"    => $validityPeriod,
                "verification_value" => $verificationValue,
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return [
            $requestNo,
            $res["ticket"],
        ];
    }

    /**
     * 绑定银行卡-推进
     * @author 王崇全
     * @date
     * @param string      $ticket    绑卡时返回的ticket
     * @param string      $validCode 短信验证码
     * @param string|null $memo
     * @return array [钱包系统卡ID,银行卡是否已认证] (银行卡是否已认证，Y：已认证；N：未认证)
     * @throws Exception
     */
    public function bindingBankCardAdvance(string $ticket, string $validCode, string $memo = null) {

        $res = $this->_sp->setBase("binding_bank_card_advance", $memo)
            ->setBiz([
                "ticket"     => $ticket,
                "valid_code" => $validCode,
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return [
            $res["card_id"],
            $res["is_verified"],
        ];
    }

    /**
     * 解绑银行卡
     * @author 王崇全
     * @date
     * @param int         $userId 用户ID
     * @param string      $cardId 卡ID
     * @param string|null $memo
     * @return string 解绑推进时需要带上此参数，ticket有效期为15分钟
     * @throws Exception
     */
    public function unbindingBankCard(int $userId, string $cardId, string $memo = null) {

        $res = $this->_sp->setBase("unbinding_bank_card", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "card_id"       => $cardId,
                "advance_flag"  => "Y",
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res["ticket"];
    }

    /**
     * 解绑银行卡-推进
     * 会同事发送验证码短信
     * @author 王崇全
     * @date
     * @param int         $userId    用户ID
     * @param string      $ticket    绑卡时返回的ticket
     * @param string      $validCode 短信验证码
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function unbindingBankCardAdvance(int $userId, string $ticket, string $validCode, string $memo = null) {

        $res = $this->_sp->setBase("unbinding_bank_card_advance", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "ticket"        => $ticket,
                "valid_code"    => $validCode,
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res;
    }

    /**
     * 查询银行卡
     * @author 王崇全
     * @date
     * @param int         $userId 用户ID
     * @param string|null $cardId 卡ID
     * @param string|null $memo
     * @return array [卡信息ID,银行编号,银行卡号,户名,卡类型,卡属性,VerifyMode是否是Sign,创建时间,安全卡标识]
     * @throws Exception
     */
    public function queryBankCard(int $userId, string $cardId = null, string $memo = null) {

        $res = $this->_sp->setBase("query_bank_card", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "card_id"       => $cardId,
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }
        if (isset($res["card_list"])) {
            $res["card_list"] = explode("|", $res["card_list"]);

            foreach ($res["card_list"] as &$v) {

                $v = explode("^", $v);

                $keys = [
                    "card_id",
                    "bank_code",
                    "bank_account_no",
                    "account_name",
                    "card_type",
                    "card_attribute",
                    "is_sign",
                    "create_time",
                    "is_safe",
                ];
                if (count($v) === 6) {
                    array_push($keys, "saving_pot_type");
                }

                $v = array_combine($keys, $v);

                $v["is_sign"] = $v["is_sign"] === "Y" ? true : false;
                $v["is_safe"] = $v["is_safe"] === "Y" ? true : false;
            }
        }

        return $res;
    }

    /**
     * 查询余额/基金份额
     * @author 王崇全
     * @date
     * @param int         $userId 用户ID
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function queryBalance(int $userId, string $memo = null) {

        $res = $this->_sp->setBase("query_balance", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "account_type"  => self::ACCOUNT_TYPE_SAVING_POT,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        //存钱罐收益
        if (isset($res["bonus"])) {
            $res["bonus"] = explode("^", $res["bonus"]);
            $res["bonus"] = array_combine([
                "yesterday",
                "this_month",
                "totle",
            ], $res["bonus"]);
        }

        return $res;
    }

    /**
     * 查询余额/基金份额-用户
     * @author 王崇全
     * @date
     * @param int         $partnerId 用户ID
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function queryBalancePartner(int $partnerId, string $memo = null) {
        $res = $this->_sp->setBase("query_balance", $memo)
            ->setBiz([
                "identity_id"   => $partnerId,
                "identity_type" => self::IDENTITY_TYPE_MEMBER_ID,
                "account_type"  => self::ACCOUNT_TYPE_BASIC,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        //存钱罐收益
        if (isset($res["bonus"])) {
            $res["bonus"] = explode("^", $res["bonus"]);
            $res["bonus"] = array_combine([
                "yesterday",
                "this_month",
                "totle",
            ], $res["bonus"]);
        }

        return $res;
    }

    /**
     * 查询收支明细
     * @author 王崇全
     * @date
     * @param int         $userId    用户ID
     * @param int|null    $startTime 开始时间
     * @param int|null    $endTime   结束时间
     * @param int         $pageNo    页码
     * @param int         $pageSize  页长
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function queryAccountDetails(int $userId, int $startTime = null, int $endTime = null, int $pageNo = DEFAULT_PAGE_NO, int $pageSize = DEFAULT_PAGE_SIZE, string $memo = null) {

        if ((isset($startTime) || isset($startTime))) {
            $now = time();
            if ($endTime ?? $now - $startTime ?? ($now - 3600 * 24 * 90) > 3600 * 24 * 90) {
                throw new Exception("时间跨度不能超过90天");
            }
        }


        $startTime = date("YmdHis", $startTime);
        $endTime   = date("YmdHis", $endTime);

        $res = $this->_sp->setBase("query_account_details", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "account_type"  => self::ACCOUNT_TYPE_SAVING_POT,
                "start_time"    => $startTime,
                "end_time"      => $endTime,
                "page_no"       => $pageNo,
                "page_size"     => $pageSize,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        if (isset($res["detail_list"])) {
            $res["detail_list"] = explode("|", $res["detail_list"]);

            foreach ($res["detail_list"] as &$v) {

                $v = explode("^", $v);

                $keys = [
                    "summary",
                    "booked_time",
                    "+/-",
                    "amount",
                    "balance",
                ];
                if (count($v) === 6) {
                    array_push($keys, "saving_pot_type");
                }

                $v = array_combine($keys, $v);
            }
        }

        return $res;
    }

    /**
     * 冻结余额
     * @author 王崇全
     * @date
     * @param int         $userId  用户ID
     * @param float       $amount  金额
     * @param string      $summary 摘要
     * @param string|null $memo
     * @return string 冻结订单号
     * @throws Exception
     */
    public function balanceFreeze(int $userId, float $amount, string $summary, string $memo = null) {

        //冻结订单号
        $outFreezeNo = $this->mkUniNo();

        $res = $this->_sp->setBase("balance_freeze", $memo)
            ->setBiz([
                "out_freeze_no" => $outFreezeNo,
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                //                "account_type"  => self::ACCOUNT_TYPE_SAVING_POT,
                "amount"        => $amount,
                "summary"       => $summary,
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $outFreezeNo;
    }

    /**
     *  解冻余额
     * @author 王崇全
     * @date
     * @param int         $userId      用户ID
     * @param string      $outFreezeNo 冻结订单号
     * @param string      $summary     摘要
     * @param float|null  $amount      金额(空,表示全额解冻)
     * @param string|null $memo
     * @return string 解冻订单号
     * @throws Exception
     */
    public function balanceUnfreeze(int $userId, string $outFreezeNo, string $summary, float $amount = null, string $memo = null) {

        //解冻订单号
        $outUnfreezeNo = $this->mkUniNo();

        $res = $this->_sp->setBase("balance_unfreeze", $memo)
            ->setBiz([
                "out_freeze_no"   => $outFreezeNo,
                "out_unfreeze_no" => $outUnfreezeNo,
                "identity_id"     => $userId,
                "identity_type"   => self::IDENTITY_TYPE_UID,
                //                "account_type"  => self::ACCOUNT_TYPE_SAVING_POT,
                "amount"          => $amount,
                "summary"         => $summary,
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $outUnfreezeNo;
    }

    /**
     *  查询冻结解冻结果
     * @author 王崇全
     * @date
     * @param string      $outFreezeNo 冻结订单号
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function queryCtrlResult(string $outFreezeNo, string $memo = null) {

        $res = $this->_sp->setBase("query_ctrl_result", $memo)
            ->setBiz([
                "out_ctrl_no" => $outFreezeNo,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res;
    }

    /**
     *  sina 页面展示用户信息
     * @author 王崇全
     * @date
     * @param int         $userId 用户ID
     * @param string|null $memo
     * @return string 重定向URL
     * @throws Exception
     */
    public function showMemberInfosSina(int $userId, string $memo = null) {

        $res = $this->_sp->setBase("show_member_infos_sina", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "resp_method"   => "1",
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res["redirect_url"];
    }

    /**
     * 查询中间账户
     * @author 王崇全
     * @date
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function queryMiddleAccount(string $memo = null) {

        $res = $this->_sp->setBase("query_middle_account", $memo)
            ->setBiz([
                "out_trade_code" => null,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        if (isset($res["account_list"])) {
            $res["account_list"] = explode("|", $res["account_list"]);

            foreach ($res["account_list"] as &$v) {

                $v = explode("^", $v);

                $keys = [
                    "out_trade_code",
                    "middle_account_no",
                    "balance",
                ];
                $v    = array_combine($keys, $v);

                if (isset(self::OUT_TRAD_CODE[ $v["out_trade_code"] ])) {
                    $v["out_trade"] = self::OUT_TRAD_CODE[ $v["out_trade_code"] ];
                }
            }
        }

        return $res;
    }

    /**
     * 修改认证手机
     * @author 王崇全
     * @date
     * @param int         $userId 用户ID
     * @param string|null $memo
     * @return string 重定向URL
     * @throws Exception
     */
    public function modifyVerifyMobile(int $userId, string $memo = null) {

        $res = $this->_sp->setBase("modify_verify_mobile", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res["redirect_url"];
    }

    /**
     * 找回认证手机
     * @author 王崇全
     * @date
     * @param int         $userId 用户ID
     * @param string|null $memo
     * @return string 重定向URL
     * @throws Exception
     */
    public function findVerifyMobile(int $userId, string $memo = null) {

        $res = $this->_sp->setBase("find_verify_mobile", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res["redirect_url"];
    }

    /**
     * 绑定认证信息
     * @author 王崇全
     * @date
     * @param int         $userId       用户ID
     * @param string      $verifyType   认证类型
     * @param string      $verifyEntity 认证内容
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function bindingVerify(int $userId, string $verifyType, string $verifyEntity, string $memo = null) {

        $res = $this->_sp->setBase("binding_verify", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "verify_type"   => $verifyType,
            ])
            ->setBizRsa([
                "verify_entity" => $verifyEntity,
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res;
    }

    /**
     * 解绑认证信息
     * @author 王崇全
     * @date
     * @param int         $userId     用户ID
     * @param string      $verifyType 认证类型
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function unbindingVerify(int $userId, string $verifyType, string $memo = null) {

        $res = $this->_sp->setBase("unbinding_verify", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "verify_type"   => $verifyType,
            ])
            ->setBizIp("client_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res;
    }

    /**
     * 查询认证信息
     * @author 王崇全
     * @date
     * @param int         $userId     用户ID
     * @param string      $verifyType 认证类型
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function queryVerify(int $userId, string $verifyType, string $memo = null) {

        $res = $this->_sp->setBase("query_verify", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "verify_type"   => $verifyType,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res;
    }

    /**
     * 我的银行卡
     * @author 王崇全
     * @date
     * @param int         $userId 用户ID
     * @param string|null $memo
     * @return string 重定向URL
     * @throws Exception
     */
    public function webBindingBankCard(int $userId, string $memo = null) {

        $res = $this->_sp->setBase("web_binding_bank_card", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        return $res["redirect_url"];
    }

    /*************************************************会员类接口-结束****************************************************/

    /*************************************************订单类接口-开始****************************************************/

    /**
     * 托管充值
     * @author 王崇全
     * @date
     * @param int         $userId    用户ID
     * @param float       $amount    金额
     * @param float       $userFee   手续费
     * @param string      $returnUrl 跳转地址
     * @param string      $summary   摘要
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function createHostingHeposit(int $userId, float $amount, string $returnUrl, float $userFee = 0, string $summary = null, string $memo = null) {

        if (!$userFee) {
            $userFee = null;
        }

        //交易订单号
        $outTradeNo = $this->mkUniNo();

        $res = $this->_sp->setBase("create_hosting_deposit", $memo, $returnUrl)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "out_trade_no"  => $outTradeNo,
                "summary"       => $summary,
                "account_type"  => self::ACCOUNT_TYPE_SAVING_POT,
                "amount"        => $amount,
                "user_fee"      => $userFee,
                // "deposit_close_time" => $depositCloseTime,
                "pay_method"    => "online_bank^{$amount}^SINAPAY,DEBIT,C",
            ])
            ->setBizIp("payer_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        try {
            $pdw   = new PayDepositWithdraw();
            $dbRes = $pdw->add($userId, $amount, $outTradeNo, $userFee, get_cilent_ip(), $summary, $res["deposit_status"]);
        } catch (Exception|\Exception $e) {
            throw new Exception("订单信息保存失败, 请重试!");
        }
        if (!$dbRes) {
            throw new Exception("订单信息保存失败, 请重试!");
        }

        return [
            "out_trade_no"   => $outTradeNo,
            "deposit_status" => $res["deposit_status"],
            "redirect_url"   => $res["redirect_url"],
        ];
    }

    /**
     * 托管充值查询
     * 起止时间和充值单号必须有一个, 都有则以单号为准
     * @author 王崇全
     * @date
     * @param int         $userId     用户ID
     * @param string|null $outTradeNo 充值订单号
     * @param int|null    $startTime  起始时间
     * @param int|null    $endTime    截止时间
     * @param int         $pageNo     页码
     * @param int         $pageSize   页长
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function queryHostingDeposit(int $userId, int $endTime = null, int $startTime = null, string $outTradeNo = null, int $pageNo = DEFAULT_PAGE_NO, int $pageSize = DEFAULT_PAGE_SIZE, string $memo = null) {

        if ((isset($startTime) || isset($endTime))) {

            $now       = time();
            $endTime   = $endTime ?? $now;
            $startTime = $startTime ?? ($now - 3600 * 24 * 30);

            if ($endTime - $startTime > 3600 * 24 * 30) {
                throw new Exception("时间跨度不能超过30天");
            }

            $startTime = date("YmdHis", $startTime);
            $endTime   = date("YmdHis", $endTime);
        } elseif (!isset($outTradeNo)) {
            throw new Exception("起止时间和充值单号必须有一个");
        }

        $res = $this->_sp->setBase("query_hosting_deposit", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "account_type"  => self::ACCOUNT_TYPE_SAVING_POT,
                "out_trade_no"  => $outTradeNo,
                "start_time"    => $startTime,
                "end_time"      => $endTime,
                "page_no"       => $pageNo,
                "page_size"     => $pageSize,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        if (isset($res["deposit_list"])) {
            $res["deposit_list"] = explode("|", $res["deposit_list"]);

            foreach ($res["deposit_list"] as &$v) {

                $v = explode("^", $v);

                $keys = [
                    "deposit_no",
                    "amount",
                    "status",
                    "deposit_time",
                    "last_modify_time",
                ];
                $v    = array_combine($keys, $v);
            }
        }

        return $res;
    }

    /**
     * 托管提现
     * @author 王崇全
     * @date
     * @param int         $userId    用户ID
     * @param float       $amount    金额
     * @param float       $userFee   手续费
     * @param string      $returnUrl 回跳地址
     * @param string|null $summary   摘要
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function createHostingWithdraw(int $userId, float $amount, string $returnUrl, float $userFee = 0, string $summary = null, string $memo = null) {

        if (!$userFee) {
            $userFee = null;
        }

        //交易订单号
        $outTradeNo = $this->mkUniNo();

        $res = $this->_sp->setBase("create_hosting_withdraw", $memo, $returnUrl)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "out_trade_no"  => $outTradeNo,
                "summary"       => $summary,
                "account_type"  => self::ACCOUNT_TYPE_SAVING_POT,
                "amount"        => $amount,
                "user_fee"      => $userFee,
            ])
            ->setBizIp("user_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        try {
            $pdw   = new PayDepositWithdraw();
            $dbRes = $pdw->add($userId, $amount * -1, $outTradeNo, $userFee, get_cilent_ip(), $summary, $res["withdraw_status"] ?? null);
        } catch (Exception|\Exception $e) {
            throw new Exception("订单信息保存失败, 请重试!");
        }
        if (!$dbRes) {
            throw new Exception("订单信息保存失败, 请重试!");
        }

        return [
            "out_trade_no" => $outTradeNo,
            "redirect_url" => $res["redirect_url"],
        ];
    }

    /**
     * 托管提现查询
     * 起止时间和充值单号必须有一个, 都有则以单号为准
     * @author 王崇全
     * @date
     * @param int         $userId     用户ID
     * @param string|null $outTradeNo 充值订单号
     * @param int|null    $startTime  起始时间
     * @param int|null    $endTime    截止时间
     * @param int         $pageNo     页码
     * @param int         $pageSize   页长
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function queryHostingWithdraw(int $userId, int $endTime = null, int $startTime = null, string $outTradeNo = null, int $pageNo = DEFAULT_PAGE_NO, int $pageSize = DEFAULT_PAGE_SIZE, string $memo = null) {

        if ((isset($startTime) || isset($endTime))) {

            $now       = time();
            $endTime   = $endTime ?? $now;
            $startTime = $startTime ?? ($now - 3600 * 24 * 30);

            if ($endTime - $startTime > 3600 * 24 * 30) {
                throw new Exception("时间跨度不能超过30天");
            }

            $startTime = date("YmdHis", $startTime);
            $endTime   = date("YmdHis", $endTime);
        } elseif (!isset($outTradeNo)) {
            throw new Exception("起止时间和提现单号必须有一个");
        }

        $res = $this->_sp->setBase("query_hosting_withdraw", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "account_type"  => self::ACCOUNT_TYPE_SAVING_POT,
                "out_trade_no"  => $outTradeNo,
                "start_time"    => $startTime,
                "end_time"      => $endTime,
                "page_no"       => $pageNo,
                "page_size"     => $pageSize,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        if (isset($res["withdraw_list"])) {
            $res["withdraw_list"] = explode("|", $res["withdraw_list"]);

            foreach ($res["withdraw_list"] as &$v) {

                $v = explode("^", $v);

                $keys = [
                    "deposit_no",
                    "amount",
                    "status",
                    "withdraw_time",
                    "last_modify_time",
                ];
                $v    = array_combine($keys, $v);
            }
        }

        return $res;
    }

    /**
     * 创建托管代收交易
     * @author 王崇全
     * @date
     * @param int         $userId    用户ID
     * @param float       $amount    金额
     * @param string      $returnUrl 回跳地址
     * @param string|null $summary   摘要
     * @param string|null $memo      备注
     * @return array
     * @throws Exception
     */
    public function createHostingCollectTrade(int $userId, float $amount, string $summary, string $returnUrl, string $memo = null) {

        //交易订单号
        $outTradeNo = $this->mkUniNo();

        $res = $this->_sp->setBase("create_hosting_collect_trade", $memo, $returnUrl)
            ->setBiz([
                "out_trade_no"        => $outTradeNo,
                //代收投资金
                "out_trade_code"      => "1001",
                "summary"             => $summary,
                "payer_id"            => $userId,
                "payer_identity_type" => self::IDENTITY_TYPE_UID,
                "pay_method "         => "online_bank^{$amount}^SINAPAY,DEBIT,C",
            ])
            ->setBizIp("payer_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        try {
            $pcp   = new PayCollectPay();
            $dbRes = $pcp->add($userId, $amount, $outTradeNo, "1001", get_cilent_ip(), "online_bank^{$amount}^SINAPAY,DEBIT,C", $summary);
        } catch (Exception|\Exception $e) {
            throw new Exception("订单信息保存失败, 请重试!");
        }
        if (!$dbRes) {
            throw new Exception("订单信息保存失败, 请重试!");
        }

        return $res;
    }

    /**
     * 创建托管代付交易
     * @author 王崇全
     * @date
     * @param int         $userId    用户ID
     * @param float       $amount    金额
     * @param string      $returnUrl 回跳地址
     * @param string|null $summary   摘要
     * @param string|null $memo      备注
     * @return array
     * @throws Exception
     */
    public function createSingleHostingPayTrade(int $userId, float $amount, string $summary, string $returnUrl, string $memo = null) {

        //交易订单号
        $outTradeNo = $this->mkUniNo();

        $res = $this->_sp->setBase("create_single_hosting_pay_trade", $memo, $returnUrl)
            ->setBiz([
                "out_trade_no"        => $outTradeNo,
                //代收投资金
                "out_trade_code"      => "2001",
                "payee_identity_id"   => $userId,
                "payee_identity_type" => self::IDENTITY_TYPE_UID,
                "account_type"        => self::ACCOUNT_TYPE_SAVING_POT,
                "amount"              => $amount,
                "summary"             => $summary,
            ])
            ->setBizIp("user_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        try {
            $pcp   = new PayCollectPay();
            $dbRes = $pcp->add($userId, $amount * -1, $outTradeNo, "2001", get_cilent_ip(), "", $summary);
        } catch (Exception|\Exception $e) {
            throw new Exception("订单信息保存失败, 请重试!");
        }
        if (!$dbRes) {
            throw new Exception("订单信息保存失败, 请重试!");
        }

        return $res;
    }

    /**
     * 创建批量托管代付交易
     * @author 王崇全
     * @date
     * @param array       $trades    [ ["userId"=>用户ID,"amount"=>金额] , ... ]
     * @param string      $returnUrl 回跳地址
     * @param string|null $summary   摘要
     * @param string|null $memo      备注
     * @return array
     * @throws Exception
     */
    public function createBatchHostingPayTrade(array $trades, string $summary, string $returnUrl, string $memo = null) {

        //支付请求号
        $outPayNo = $this->mkUniNo();

        $tradeList = "";
        foreach ($trades as &$v) {
            //交易订单号
            $v["out_trade_no"] = $this->mkUniNo();
            $tradeList         .= "\${$v['out_trade_no']}~{$v['user_id']}~".self::IDENTITY_TYPE_UID."~".self::ACCOUNT_TYPE_SAVING_POT."~{$v['amount']}~~{$summary}~~~~";
        }
        $tradeList = substr($tradeList, 1);

        $res = $this->_sp->setBase("create_batch_hosting_pay_trade", $memo, $returnUrl)
            ->setBiz([
                "out_pay_no"     => $outPayNo,
                //代收投资金
                "out_trade_code" => "2001",
                "trade_list"     => $tradeList,
                "notify_method"  => "single_notify",
            ])
            ->setBizIp("user_ip")
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        try {
            $pcp   = new PayCollectPay();
            $dbRes = 0;
            foreach ($trades as &$v) {
                $dbRes += $pcp->add($v["user_id"], $v['amount'] * -1, $v["out_trade_no"], "2001", get_cilent_ip(), "", $summary);
            }
        } catch (Exception|\Exception $e) {
            throw new Exception("订单信息保存失败, 请重试!");
        }
        if ($dbRes != count($trades)) {
            throw new Exception("订单信息保存失败, 请重试!");
        }

        return $res;
    }

    /**
     * 托管交易查询
     * 起止时间和充值单号必须有一个, 都有则以单号为准
     * @author 王崇全
     * @date
     * @param int|null         $userId     用户ID
     * @param string|null $outTradeNo 充值订单号
     * @param int|null    $startTime  起始时间
     * @param int|null    $endTime    截止时间
     * @param int         $pageNo     页码
     * @param int         $pageSize   页长
     * @param string|null $memo
     * @return array
     * @throws Exception
     */
    public function queryHostingTrade(int $userId = null, int $endTime = null, int $startTime = null, string $outTradeNo = null, int $pageNo = DEFAULT_PAGE_NO, int $pageSize = DEFAULT_PAGE_SIZE, string $memo = null) {

        if (!$outTradeNo && !$userId) {
            throw new Exception("当交易订单号为空时，用户标志和标志类型不能为空");
        }

        if ((isset($startTime) || isset($endTime))) {

            $now       = time();
            $endTime   = $endTime ?? $now;
            $startTime = $startTime ?? ($now - 3600 * 24 * 30);

            if ($endTime - $startTime > 3600 * 24 * 30) {
                throw new Exception("时间跨度不能超过30天");
            }

            $startTime = date("YmdHis", $startTime);
            $endTime   = date("YmdHis", $endTime);
        } elseif (!isset($outTradeNo)) {
            throw new Exception("交易号和时间至少一项存在，同时存在以交易号为准");
        }

        $res = $this->_sp->setBase("query_hosting_trade", $memo)
            ->setBiz([
                "identity_id"   => $userId,
                "identity_type" => self::IDENTITY_TYPE_UID,
                "account_type"  => self::ACCOUNT_TYPE_SAVING_POT,
                "out_trade_no"  => $outTradeNo,
                "start_time"    => $startTime,
                "end_time"      => $endTime,
                "page_no"       => $pageNo,
                "page_size"     => $pageSize,
            ])
            ->send();

        if ($res["response_code"] != "APPLY_SUCCESS") {
            throw new Exception($res["response_message"]);
        }

        if (isset($res["trade_list"])) {
            $res["trade_list"] = explode("|", $res["trade_list"]);

            foreach ($res["trade_list"] as &$v) {

                $v = explode("^", $v);

                $keys = [
                    "out_trade_no",
                    "summary",
                    "amount",
                    "status",
                    "trade_time",
                    "last_modify_time",
                ];
                if (count($v) === 7) {
                    $keys[] = "amount_completed";
                }
                $v = array_combine($keys, $v);
            }
        }

        return $res;
    }


    /**
     * 生成唯一序列号
     * @author 王崇全
     * @date
     * @return string
     */
    protected function mkUniNo() {
        $date = new \DateTime();

        return uniqid($date->format("ymdHisu"));
    }

}
