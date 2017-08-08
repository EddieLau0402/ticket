<?php

namespace Eddie\Ticket;


use Eddie\Ticket\Providers\Yuanhui;

class TicketManager
{
    public function provider($provider)
    {
        switch (strtolower($provider)) {
            case 'yuanhui':
                $config = config('ticket.yuanhui');
                return new Yuanhui($config);


            default:
                throw new \Exception('找不到相应的provider', 500);
        }
    }
}