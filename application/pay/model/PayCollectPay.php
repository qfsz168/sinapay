<?php
/**
 * Created by PhpStorm.
 * User: sdjs-014
 * Date: 2017/8/17
 * Time: 12:04
 */

namespace app\pay\model;

use think\Model;

/**
 * Class 代收代付记录表
 * @package app\pay\model
 */
class PayCollectPay extends Model {

    public function initialize() {
        parent::initialize();
    }

    /**
     * 添加
     * @author 王崇全
     * @date
     * @param int    $userId       用户编号
     * @param float  $amount       金额(正,代收;负,代付)
     * @param string $tradeNo      订单号
     * @param string $outTradeCode 外部业务码
     * @param int    $ip           客户端IP
     * @param string $payMethod    支付方式
     * @param string $summary      摘要
     * @param string $status       状态
     * @return false|int
     */
    public function add(int $userId, float $amount, string $tradeNo, string $outTradeCode, int $ip, string $payMethod, string $summary, string $status = null) {

        $data = [
            "user_id"        => $userId,
            "amount"         => $amount,
            "trade_no"       => $tradeNo,
            "out_trade_code" => $outTradeCode,
            "payer_ip"       => $ip,
            "pay_method"     => $payMethod,
            "summary"        => $summary,
            "create_time"    => date("Y-m-d H:i:s"),
        ];
        if ($status) {
            $data["status"] = $status;
        }

        return $this->insert($data);
    }

    /**
     * 修改状态
     * @author 王崇全
     * @date
     * @param string $tradeNo 订单号
     * @param string $status  状态
     * @param float  $amount  金额
     * @return false|int
     */
    public function modify(string $tradeNo, string $status, float $amount) {

        $info = $this->getInfo($tradeNo);
        if ($info["out_trade_code"] === "2001") {
            $amount *= -1;
        }

        return $this->isUpdate()
            ->save([
                "status"           => $status,
                "amount"           => $amount,
                "last_modify_time" => date("Y-m-d H:i:s"),
            ], [
                "trade_no" => $tradeNo,
            ]);
    }

    /**
     * 获取信息
     * @author 王崇全
     * @date
     * @param string $tradeNo
     * @return array
     */
    public function getInfo(string $tradeNo) {

        $info = $this->field([
            "user_id",
            "amount",
            "out_trade_code",
            "summary",
            "status",
        ])
            ->where([
                "trade_no" => $tradeNo,
            ])
            ->find();

        if (!$info) {
            return [];
        }

        return $info->toArray();
    }

}