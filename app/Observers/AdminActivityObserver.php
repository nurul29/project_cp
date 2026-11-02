<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class AdminActivityObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created', array_keys($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $this->record($model, 'updated', array_keys($model->getChanges()));
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', []);
    }

    /**
     * @param  array<int, string>  $fields
     */
    private function record(Model $model, string $event, array $fields): void
    {
        $actor = auth()->user();
        if (! $actor?->isAdmin() || $model instanceof ActivityLog) {
            return;
        }

        $fields = array_values(array_diff($fields, [
            'password',
            'remember_token',
            'updated_at',
            'created_at',
        ]));
        $modelName = class_basename($model);

        ActivityLog::query()->create([
            'actor_id' => $actor->getKey(),
            'event' => $event,
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'description' => ucfirst($event).' '.$modelName.' #'.$model->getKey(),
            'properties' => ['fields' => $fields],
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}
