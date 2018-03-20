<?php

namespace oliveready7\LaravelSes\Controllers;

use Illuminate\Http\Request;
use oliveready7\LaravelSes\Models\EmailOpen;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controller;
use Carbon\Carbon;

class OpenController extends BaseController
{
    public function open($beaconIdentifier) {
        try {
            $open = EmailOpen::whereBeaconIdentifier($beaconIdentifier)->firstOrFail();
        }catch(ModelNotFoundException $e){
            return response()->json(['success' => false, 'errors' => ['Invalid Beacon']], 422);
        }

        $open->opened_at = Carbon::now();
        $open->save();

        return redirect(config('app.url')."/laravel-ses/to.png");
    }
}
