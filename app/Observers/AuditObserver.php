<?php

namespace App\Observers;

use App\Services\AuditLogger;

/**
 * Generic observer — attach to any auditable model.
 */
class AuditObserver
{
    public function created($model)
    {
        AuditLogger::log($model, 'created');
    }

    public function updated($model)
    {
        AuditLogger::log($model, 'updated');
    }

    public function deleted($model)
    {
        AuditLogger::log($model, 'deleted');
    }

    public function restored($model)
    {
        AuditLogger::log($model, 'restored');
    }
}
