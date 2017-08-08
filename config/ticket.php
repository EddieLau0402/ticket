<?php

return [
    /*
     * 源慧API - config
     */
    'yuanhui' => [
        /*
         * 客户/账号
         */
        'cid' => env('YUANHUI_CID', ''),

        /*
         * 获取 API 校验
         */
        'appkey' => env('YUANHUI_APP_KEY', ''),

        /*
         * 服务地址
         */
        'url' => env('YUANHUI_API_DOMAIN', 'http://i.eswapi.com/API/'),

        /*
         * 资源 :
         */
        'resource' => [ // productid(资源ID) => 奖品资源名称
            '10305001' => '蓝券(限2D)',
            '10305002' => '橙券(2D、3D通兑)',
        ],

    ],

];
