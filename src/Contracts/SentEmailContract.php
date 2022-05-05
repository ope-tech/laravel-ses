<?php

namespace Juhasev\LaravelSes\Contracts;

interface SentEmailContract
{
    public function setDeliveredAt($time);
    
    public function emailOpen();

    public function emailLinks();

    public function emailBounce();

    public function emailComplaint();

    public function emailReject();

    public function getId();
}