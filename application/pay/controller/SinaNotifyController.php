<?php

namespace app\pay\controller;

use app\pay\model\PayCollectPay;
use app\pay\model\PayDepositWithdraw;
use sinapay\Sinapay;
use think\Controller;
use think\Exception;
use think\Request;

class SinaNotifyController extends Controller {

    public function _initialize() {
        parent::_initialize();
    }

    /**
     * 所有涉及到回调的api接口入口
     * 新浪异步通知方式为post通知。
     * @author 王崇全
     * @date
     * @return void
     * @throws Exception
     */
    public function index() {

        $data = Request::instance()
            ->param(true);

        $result = $this->validate($data, [
            //必须有签名
            'sign' => 'require',
        ]);
        if (true !== $result) {
            throw new Exception($result);
        }

        ksort($data);

        $sp = new Sinapay();

        //记录返回内容
        $sp->log("收到新浪支付异步通知(无内容,则表示签名校验失败):\r\n");

        $sp->checkNotifySign($_POST);

        $sp->log(var_export($data, true));

        $ok = false;
        switch ($data["notify_type"]) {

            //交易结果通知
            case "trade_status_sync":

                $notEnd = [
                    "TRADE_FINISHED",
                    "TRADE_CLOSED",
                    "TRADE_FAILED",
                ];

                if (!in_array($data["trade_status"], $notEnd)) {
                    die('success');
                }

                $pcp = new PayCollectPay();

                $dbRes = $pcp->modify($data["outer_trade_no"], $data["trade_status"], $data["trade_amount"]);

                if ($dbRes) {
                    $ok = true;
                }
            break;
            //充值结果通知
            case "deposit_status_sync":

                $pdw   = new PayDepositWithdraw();
                $dbRes = $pdw->modify($data["outer_trade_no"], $data["deposit_status"], $data["deposit_amount"]);

                if ($dbRes) {
                    $ok = true;
                }
            break;
            //提现结果通知
            case "withdraw_status_sync":

                $pdw   = new PayDepositWithdraw();
                $dbRes = $pdw->modify($data["outer_trade_no"], $data["withdraw_status"], $data["withdraw_amount"] * -1);

                if ($dbRes) {
                    $ok = true;
                }
            break;
            default:
        }

        if ($ok) {
            // 如果回调成功，需要输出SUCCESS告知新浪回调服务器，已经收到异步通知。
            die('success');
        }

    }

    public function callback() {
    }

}
