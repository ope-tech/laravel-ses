<?php

namespace Juhasev\LaravelSes\Controllers;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\ModelResolver;

class LinkController extends BaseController
{
    /**
     * Link clicked
     *
     * @param $linkIdentifier
     * @return RedirectResponse
     * @throws Exception
     */

    public function click($linkIdentifier)
    {
        try {
            $link = ModelResolver::get('EmailLink')::whereLinkIdentifier($linkIdentifier)->firstOrFail();

            $link->setClicked(true)->incrementClickCount();

            return redirect($link->original_url);

        } catch (ModelNotFoundException $e) {

            Log::error("Could not find link ($linkIdentifier). Email link click count not incremented!");
        }
    }
}
