<?php

namespace App\Services\Carriers\Adapters;

use App\Services\Carriers\BaseCarrierAdapter;

class UpsAdapter extends BaseCarrierAdapter
{
    public function code(): string { return 'ups'; }
    public function name(): string { return 'UPS'; }
    protected function envKeys(): array { return ['CARRIER_UPS_KEY', 'CARRIER_UPS_SECRET']; }
    protected function endpoint(): string { return 'https://onlinetools.ups.com/api/track/v1/details'; }
}
