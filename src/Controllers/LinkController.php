<?php

namespace Juhasev\LaravelSes\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Juhasev\LaravelSes\Models\EmailLink;

class LinkController extends BaseController
{
    public function click($linkIdentifier)
    {
        $link = EmailLink::whereLinkIdentifier($linkIdentifier)->firstOrFail();
        $link->setClicked(true)->incrementClickCount();
        return redirect($link->original_url);
    }
}
