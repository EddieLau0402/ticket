<?php

namespace Eddie\Ticket\Providers;

use Eddie\Ticket\Util;
use Eddie\Ticket\TicketInterface;

class Yuanhui implements TicketInterface
{
    use Util;


    /*
     * API uri
     */
    //// http://xxxxxxxx/API/TicketGet.ashx
    const API_TICKET_GET = 'TicketGet.ashx'; // 电影票提取


    /**
     * 服务地址
     *
     * @var
     */
    protected $server;

    /**
     * 获取 API 校验
     *
     * @var
     */
    protected $appkey;

    /**
     * 客户/账号
     *
     * @var
     */
    protected $cid;

    /**
     * 资源
     *
     * @var
     */
    protected $resource;

    /**
     * 调试开关
     *
     * @var bool
     */
    protected $debug_mode = false;

    /**
     * 订单流水号
     *
     * @var
     */
    protected $order_id;

    /**
     * 手机号
     *
     * @var
     */
    protected $mobile;

    /**
     * 电影票
     *
     * @var
     */
    protected $ticket;



    /**
     * Yuanhui constructor.
     *
     * @author Eddie
     *
     * @param $config
     */
    public function __construct($config)
    {
        if (!is_array($config))
            throw new \Exception('请设置好参数并且配置参数必须是数组', 500);

        if (!$config['cid'])
            throw new \Exception('缺少cid参数', 500);

        if (!$config['appkey'])
            throw new \Exception('缺少appkey参数', 500);

        if (!$config['url'])
            throw new \Exception('缺少url参数', 500);


        $this->server = $config['url'];
        $this->appkey = $config['appkey'];
        $this->cid = $config['cid'];
        $this->resource = $config['resource'];
    }


    /**
     * Open debug mode.
     *
     * @author Eddie
     *
     * @return $this
     */
    public function debug()
    {
        $this->debug_mode = true;
        return $this;
    }

    /**
     * Setter - set mobile.
     *
     * @author Eddie
     *
     * @param $mobile
     * @return $this
     */
    public function mobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * Setter - set order_id.
     *
     * @param $order_id
     * @return $this
     */
    public function orderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * Setter - set ticket.
     *
     * @author Eddie
     *
     * @param $ticket
     * @return $this
     */
    public function ticket($ticket)
    {
        $this->ticket = $ticket;
        return $this;
    }


    /**
     * 电影票提取
     *
     * @author Eddie
     *
     * @param null ticket
     * @return array
     * @throws \Exception
     */
    public function extract($ticket = null)
    {
        if (!$this->order_id) {
            throw new \Exception('订单号不能为空', 422);
        }
        if (!$this->mobile) {
            throw new \Exception('手机号不能为空', 422);
        }
        if (!$this->ticket) {
            if (!$ticket) {
                throw new \Exception('电影票不能为空', 422);
            }
            $this->ticket($ticket);
        }

        $params = [
            'cid' => $this->cid,
            'productid' => $this->getTicketId(),
            'orderid' => $this->order_id,
            'timestamps' => $this->getMsec(), // 精确到毫秒
            'mob' => $this->mobile
        ];

        /*
         * 签名
         */
        $params['sign'] = $this->signature($params);

        $url = $this->server . self::API_TICKET_GET;

        $response = $this->request($url, $params, 'POST');

        if ($this->debug_mode) {
            \Log::info('-------------------------------------------------');
            \Log::info('Request API: '.$url);
            \Log::info('Request params: '. print_r($params, true));
            \Log::info('Response: '. print_r(json_decode($response, true), true));
            \Log::info('-------------------------------------------------');
        }

        return $this->transform($response);
    }

    /**
     * Response formater.
     *
     * @author Eddie
     *
     * @param $response
     * @return array $return
     */
    public function transform($response)
    {
        $result = json_decode($response);

        if (!$result) return $result;

        $return = [
            'provider' => 'Yuanhui',
            'success'  => $result->Success,
            'msg'      => $result->Msg,
            'code'     => $result->Code,
        ];
        if ($result->Code == '1001') {
            $return['order_sn'] = $result->OutOrderId;
            $return['ticket_code'] = $result->TicketCodeData;
        }

        return $return;
    }


    /**
     * Return signature string.
     *
     * 签名机制 :
     *     请求参数列表中，除sign外其他必填参数均需要参加验签;
     *     请求列表中的所有必填参数的参数值与APPKEY经过按值的字符串格式从小到大排序(字符串格式排序)后, 直接首尾相接连接为一个字符串,
     *     然后用md5指定的加密方式进行加密。
     *
     *
     * @author Eddie
     *
     * @param $params
     * @return string
     */
    private function signature($params)
    {
        /*
         * 去除 非必选参数
         */
        //unset($params['recallurl']);

        /*
         * Generate signature.
         */
        $signArr = array_values($params);
        $signArr[] = $this->appkey;
        sort($signArr, SORT_STRING);

        return strtoupper(md5(implode($signArr)));
    }

    /**
     * Get micro-seconds.
     *
     * @author Eddie
     *
     * @return bool|string
     */
    private function getMsec()
    {
        list($msec, $sec) = explode(' ', microtime());

        return date('YmdHis' . (sprintf('%03d', $msec*1000)), $sec);
    }

    /**
     * Return productid.
     *
     * @author Eddie
     *
     * @return $productid
     */
    private function getTicketId()
    {
        if ($this->ticket) {
            $arr = array_flip($this->resource);
            if (array_key_exists($this->ticket, $arr)) {
                return $arr[$this->ticket];
            }
            else {
                throw new \Exception('没有对应的资源!', 500);
            }
        }
    }

}
