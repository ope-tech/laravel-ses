<?php

namespace oliveready7\LaravelSes\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use oliveready7\LaravelSes\Models\EmailLink;

class LinkController extends Controller {

    public function click($linkIdentifier) {
        $link = EmailLink::whereLinkIdentifier($linkIdentifier)->firstOrFail();
        $link->setClicked(true)->incrementClickCount();
        return redirect($link->original_url);
    }

}
