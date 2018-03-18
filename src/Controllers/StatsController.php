<?php

namespace oliveready7\LaravelSes\Controllers;
use Illuminate\Routing\Controller;
use SesMail;

class StatsController extends Controller
{
    public function statsForBatch($batchName) {
        return ['success' => true, 'data' => SesMail::statsForBatch($batchName)];
    }

    public function statsForEmail($email) {
        return ['success' => true, 'data' => SesMail::statsForEmail($email)];
    }
}
