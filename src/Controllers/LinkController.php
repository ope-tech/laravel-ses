<?php

namespace Juhasev\LaravelSes\Controllers;

use Juhasev\LaravelSes\Models\EmailLink;

class LinkController extends BaseController
{
    /**
     * Link clicked
     *
     * @param $linkIdentifier
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */

    public function click($linkIdentifier)
    {
        $link = EmailLink::whereLinkIdentifier($linkIdentifier)->firstOrFail();
        $link->setClicked(true)->incrementClickCount();
        return redirect($link->original_url);
    }
}
