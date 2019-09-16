<?php

namespace NotificationChannels\ClickSend;

class ClickSendMessage {
    /**
     * The phone number the message should be sent from.
     *
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * The message content.
     *
     * @var string
     */
    private $content = '';

    /**
     * @param string      $to
     * @param  string     $content
     * @param null|string $from
     */
    public function __construct( string $to, string $content, ?string $from = null ) {
        $this->to      = $to;
        $this->content = $content;
        $this->from    = $from;
    }

    /**
     * @return string
     */
    public function getFrom(): ?string {
        return $this->from;
    }

    /**
     * @param string $from
     *
     * @return ClickSendMessage
     */
    public function setFrom(string $from): ClickSendMessage {
        $this->from = $from;

        return $this;
    }

    /**
     * @return string
     */
    public function getTo(): string {
        return $this->to;
    }

    /**
     * @param string $to
     *
     * @return ClickSendMessage
     */
    public function setTo(string $to): ClickSendMessage {
        $this->to = $to;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return ClickSendMessage
     */
    public function setContent(string $content): ClickSendMessage {
        $this->content = $content;

        return $this;
    }
}
