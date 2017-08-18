<?php

namespace app\pay\controller;

use app\pay\model\Sina;
use think\Controller;

class SinaController extends Controller {

    public function _initialize() {
        parent::_initialize();
    }

    public function index() {

        $sp = new Sina();

        // $a = $sp->setRealName("2008432134", "王崇全","37112219890402633x");

        // $a = $sp->setPayPassword("2008432134", "http://baidu.com");

        // $a  = $sp->bindingBankCard("2011104217", "GDB", "6214624321000295938", "王崇全", "15688447369", "山东省", "济南市");

        //199119
        // $a = $sp->bindingBankCardAdvance("eade3de0cd404cc0abedb3b79c0b5f7f", "861374");

        // $a  = $sp->unbindingBankCard("2011104217","199129");


        // $a = $sp->unbindingBankCardAdvance("2011104217", "10abfcc728a8416e9d53d0a1769be0f1", "825818");

        // $a = $sp->queryBankCard("2011104217");

        //1708161048551438715993b29723286
        // $a = $sp->createHostingHeposit("2011104217", 1000.25, "http://120.24.0.243:8000", 0.99, "充值测试", "memo测试");

        // $a = $sp->createHostingWithdraw("2011104217", 2.56, "http://120.24.0.243:8000", 1.25, "提现测试");

        // $a = $sp->queryHostingWithdraw("2011104217", time());

        //$a = $sp->createHostingCollectTrade("2011104217", 1.5, "测试项目投资", "http://www.baidu.com");

        //        $a = $sp->createBatchHostingPayTrade([
        //            [
        //                "user_id" => "2011104217",
        //                "amount"  => 10,
        //            ],
        //            [
        //                "user_id" => "2008432134",
        //                "amount"  => 20,
        //            ],
        //        ], "测试项目投资", "http://www.baidu.com");

        //$a = $sp->createHostingCollectTrade("2011104217", 1.5, "测试项目投资", "http://www.baidu.com");

        $a = $sp->queryHostingTrade("2008432134", time());

        var_dump($a);


        $b = $sp->queryMiddleAccount();
        var_dump($b);

    }

}
