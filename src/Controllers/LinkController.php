<?php

namespace Juhasev\LaravelSes\Controllers;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Contracts\EmailLinkContract;
use Juhasev\LaravelSes\Factories\EventFactory;
use Juhasev\LaravelSes\ModelResolver;

class LinkController extends BaseController
{
    /**
     * Link clicked
     *
     * @param $linkIdentifier
     * @return RedirectResponse|Redirector
     * @throws Exception
     */
    public function click($linkIdentifier)
    {
        try {
            $emailLink = ModelResolver::get('EmailLink')::whereLinkIdentifier($linkIdentifier)->firstOrFail();

            $emailLink->setClicked(true)->incrementClickCount();

            $this->sendEvent($emailLink);

            return redirect($emailLink->originalUrl());

        } catch (ModelNotFoundException $e) {

            Log::info("Could not find link ($linkIdentifier). Email link click count not incremented!");

            abort(404);
        }
    }

    /**
     * Sent event to listeners
     *
     * @param EmailLinkContract $emailLink
     */

    protected function sendEvent(EmailLinkContract $emailLink)
    {
        event(EventFactory::create('Link', 'EmailLink', $emailLink->getId()));
    }
}
