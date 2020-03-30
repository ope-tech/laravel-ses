<?php

namespace Juhasev\LaravelSes\Contracts;

interface EmailLinkContract
{
    public function sentEmail();
    public function setClicked($clicked);
    public function incrementClickCount();
}