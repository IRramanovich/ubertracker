<?php
/**
 * Created by PhpStorm.
 * User: iramanovich
 * Date: 17.4.17
 * Time: 13.03
 */

namespace App\Classes\Socket\Base;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


class BaseSocket implements MessageComponentInterface
{
    public function onOpen(ConnectionInterface $conn){

    }

    public function onMessage(ConnectionInterface $from, $msg){

    }

    public function onClose(ConnectionInterface $conn){

    }

    public function onError(ConnectionInterface $conn, \Exception $e){

    }
}