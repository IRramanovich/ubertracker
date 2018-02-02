<?php
/**
 * Created by PhpStorm.
 * User: iramanovich
 * Date: 17.4.17
 * Time: 17.48
 */

namespace App\Classes\Socket\Base;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class BasePusher implements  WampServerInterface
{
    protected $subscribedTopics = [];

    public function getSubscribedTopics(){
        return $this->subscribedTopics;
    }

    public function addSubscribedTopic($topic){
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    public function onSubscribe(ConnectionInterface $conn, $topic){
        $this->addSubscribedTopic($topic);
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic){

    }

    public function onOpen(ConnectionInterface $conn){
//        echo "New connection ({$conn->resourceId})\n";
    }

    public function onClose(ConnectionInterface $conn){
//        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params){
        $conn->callError($id, $topic, "You are not allowed to make calls")->close();
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible){
        $conn->close();
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e){
        echo "An error has occured: {$e->getMessage()}\n";
        $conn->close();
    }
}