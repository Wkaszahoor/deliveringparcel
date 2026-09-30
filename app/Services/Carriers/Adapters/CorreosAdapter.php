<?php

namespace App\Services\Carriers\Adapters;

use App\Services\Carriers\BaseCarrierAdapter;

class CorreosAdapter extends BaseCarrierAdapter
{
    public function code(): string { return 'correos'; }
    public function name(): string { return 'Correos'; }
    protected function envKeys(): array { return ['CARRIER_CORREOS_KEY']; }
    protected function endpoint(): string { return 'https://api.correos.es/services/v1/track'; }
}
