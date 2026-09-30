<?php

namespace App\Services\Carriers\Adapters;

use App\Services\Carriers\BaseCarrierAdapter;

class DhlAdapter extends BaseCarrierAdapter
{
    public function code(): string { return 'dhl'; }
    public function name(): string { return 'DHL'; }
    protected function envKeys(): array { return ['CARRIER_DHL_KEY', 'CARRIER_DHL_SECRET']; }
    protected function endpoint(): string { return 'https://api.dhl.com/track/shipments'; }
}
