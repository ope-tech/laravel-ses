<?php

namespace Juhasev\LaravelSes\Controllers;

use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Contracts\EmailOpenContract;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\Factories\EventFactory;
use Juhasev\LaravelSes\ModelResolver;

class OpenController extends BaseController
{
    /**
     * Tracking pixel fired
     *
     * @param $beaconIdentifier
     * @return JsonResponse|RedirectResponse|Redirector
     * @throws Exception
     */
    public function open($beaconIdentifier)
    {
        try {
            $emailOpen = ModelResolver::get('EmailOpen')::whereBeaconIdentifier($beaconIdentifier)->firstOrFail();
            $emailOpen->opened_at = Carbon::now();
            $emailOpen->save();

            $this->sendEvent($emailOpen);

        } catch (ModelNotFoundException $e) {

            Log::info("Could not find sent email with beacon identifier ($beaconIdentifier). Email open could not be recorded!");

            return response()->json([
                'success' => false,
                'errors' => ['Invalid Beacon']
            ], 404);
        }

        // Serve the actual image
        return redirect(config('app.url')."/ses/to.png");
    }
    
    /**
     * Sent event to listeners
     *
     * @param EmailOpenContract $emailOpen
     */
    protected function sendEvent(EmailOpenContract $emailOpen)
    {
        event(EventFactory::create('Open', 'EmailOpen', $emailOpen->getId()));
    }
}
