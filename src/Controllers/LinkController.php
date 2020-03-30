<?php

namespace Juhasev\LaravelSes\Controllers;

use Illuminate\Http\RedirectResponse;
use Juhasev\LaravelSes\ModelResolver;

class LinkController extends BaseController
{
    /**
     * Link clicked
     *
     * @param $linkIdentifier
     * @return RedirectResponse
     * @throws \Exception
     */

    public function click($linkIdentifier)
    {
        $link = ModelResolver::get('EmailLink')::whereLinkIdentifier($linkIdentifier)->firstOrFail();
        $link->setClicked(true)->incrementClickCount();
        return redirect($link->original_url);
    }
}
