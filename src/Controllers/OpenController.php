<?php

namespace Juhasev\LaravelSes\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Juhasev\LaravelSes\Models\EmailOpen;

class OpenController extends BaseController
{
    /**
     * Tracking pixel fired
     *
     * @param $beaconIdentifier
     * @return JsonResponse|RedirectResponse
     */

    public function open($beaconIdentifier)
    {
        try {
            $open = EmailOpen::whereBeaconIdentifier($beaconIdentifier)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'errors' => ['Invalid Beacon']], 422);
        }

        $open->opened_at = Carbon::now();
        $open->save();

        // Server the actual image
        return redirect(config('app.url')."/laravel-ses/to.png");
    }
}
