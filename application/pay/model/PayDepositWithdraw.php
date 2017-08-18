<?php
/**
 * Created by PhpStorm.
 * User: sdjs-014
 * Date: 2017/8/17
 * Time: 12:05
 */

namespace app\pay\model;

use think\Model;

class PayDepositWithdraw extends Model {

    /**
     * 添加
     * @author 王崇全
     * @date
     * @param int    $userId  用户编号
     * @param float  $amount  金额
     * @param string $tradeNo 订单号
     * @param float  $fee     手续费
     * @param int    $ip      客户端IP
     * @param string $summary 摘要
     * @param string $status  状态
     * @return false|int
     */
    public function add(int $userId, float $amount, string $tradeNo, float $fee, int $ip, string $summary, string $status = null) {

        $data = [
            "trade_no"    => $tradeNo,
            "user_id"     => $userId,
            "user_fee"    => $fee,
            "amount"      => $amount,
            "summary"     => $summary,
            "ip"          => $ip,
            "create_time" => date("Y-m-d H:i:s"),
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
            "user_fee",
            "amount",
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