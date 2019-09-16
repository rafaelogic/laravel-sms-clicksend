<?php

namespace NotificationChannels\ClickSend\Test;

use NotificationChannels\ClickSend\ClickSendMessage;
use PHPUnit\Framework\TestCase;

class ClickSendMessageTest extends TestCase {
    public function testCreateInstance() {
        $message = new ClickSendMessage('to', 'message', 'from');

        $this->assertEquals('to', $message->getTo());
        $this->assertEquals('message', $message->getContent());
        $this->assertEquals('from', $message->getFrom());
    }

    public function testFromArgumentIsOptional() {
        $message = new ClickSendMessage('to', 'message');

        $this->assertEquals('to', $message->getTo());
        $this->assertEquals('message', $message->getContent());
        $this->assertNull($message->getFrom());
    }
}
