<?php

namespace Juhasev\LaravelSes\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Invalid Beacon']
            ], 404);
        }

        $open->opened_at = Carbon::now();
        $open->save();

        // Server the actual image
        return redirect(config('app.url')."/laravel-ses/to.png");
    }
}
