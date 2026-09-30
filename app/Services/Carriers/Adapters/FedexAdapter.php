<?php

namespace App\Services\Carriers\Adapters;

use App\Services\Carriers\BaseCarrierAdapter;

class FedexAdapter extends BaseCarrierAdapter
{
    public function code(): string { return 'fedex'; }
    public function name(): string { return 'FedEx'; }
    protected function envKeys(): array { return ['CARRIER_FEDEX_KEY', 'CARRIER_FEDEX_SECRET']; }
    protected function endpoint(): string { return 'https://apis.fedex.com/track/v1/trackingnumbers'; }
}
