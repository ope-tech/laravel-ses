<?php

namespace Juhasev\LaravelSes\Factories\Events;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\SerializesModels;
use Juhasev\LaravelSes\Factories\BaseEvent;
use Juhasev\LaravelSes\ModelResolver;

class SesOpenEvent extends BaseEvent
{
    use SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     *
     * @param string $modelName
     * @param int $modelId
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function __construct(string $modelName, int $modelId)
    {
        $this->data = ModelResolver::get($modelName)::with([
            'sentEmail:id,message_id,email,batch_id,sent_at,delivered_at',
            'sentEmail.batch'
        ])->findOrFail($modelId)->toArray();
    }
}