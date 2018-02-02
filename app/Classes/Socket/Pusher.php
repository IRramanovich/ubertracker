<?php
/**
 * Created by PhpStorm.
 * User: iramanovich
 * Date: 17.4.17
 * Time: 18.09
 */

namespace App\Classes\Socket;

use App\Classes\Socket\Base\BasePusher;
use ZMQContext;

class Pusher extends BasePusher
{
    static function sentDataToServer(array $data){
        $context = new ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');

        $socket->connect('tcp://127.0.0.1:5555');

        $data = json_encode($data);

        $socket->send($data);
    }

    public function broadcast($jsonDataToSend){
        $aDataToSend = json_decode($jsonDataToSend, true);

        $subscriberTopics = $this->getSubscribedTopics();

        if( isset($subscriberTopics[$aDataToSend['topic_id']])){
            $topic = $subscriberTopics[$aDataToSend['topic_id']];
            $topic->broadcast($aDataToSend);
        }
    }
}