<?php

namespace Juhasev\LaravelSes\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\ModelResolver;

class OpenController extends BaseController
{
    /**
     * Tracking pixel fired
     *
     * @param $beaconIdentifier
     * @return JsonResponse|RedirectResponse
     * @throws Exception
     */

    public function open($beaconIdentifier)
    {
        try {
            $open = ModelResolver::get('EmailOpen')::whereBeaconIdentifier($beaconIdentifier)->firstOrFail();
            $open->opened_at = Carbon::now();
            $open->save();

        } catch (ModelNotFoundException $e) {

            Log::error("Could not find sent email with beacon identifier ($beaconIdentifier). Email open could not be recoreded");

            return response()->json([
                'success' => false,
                'errors' => ['Invalid Beacon']
            ], 404);
        }
        
        // Server the actual image
        return redirect(config('app.url')."/laravel-ses/to.png");
    }
}
