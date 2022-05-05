<?php

namespace Juhasev\LaravelSes\Contracts;

interface EmailLinkContract
{
    public function sentEmail();

    public function setClicked(bool $clicked);

    public function incrementClickCount();

    public function getId();

    public function originalUrl();
}