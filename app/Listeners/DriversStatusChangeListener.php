<?php

namespace App\Listeners;

use App\Events\DriversStatusChange;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class DriversStatusChangeListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */

    public $socket;

    public function __construct()
    {
//        $this->socket = stream_socket_server("tcp://92.222.97.212:3300", $errno, $errstr);
//
//        if (!$this->socket) {
//            die("$errstr ($errno)\n");
//        }
    }

    /**
     * Handle the event.
     *
     * @param  vent=DriversStatusChange  $event
     * @return void
     */
    public function handle(DriversStatusChange $event)
    {
//        while ($connect = stream_socket_accept($this->socket, -1)) {
//            fwrite($connect, "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\nConnection: close\r\n\r\nПривет");
//            fclose($connect);
//        }
    }
}
