<?php

namespace App\Services\Carriers\Adapters;

use App\Services\Carriers\BaseCarrierAdapter;

class AftershipAdapter extends BaseCarrierAdapter
{
    public function code(): string { return 'aftership'; }
    public function name(): string { return 'Aftership'; }
    protected function envKeys(): array { return ['CARRIER_AFTERSHIP_KEY']; }
    protected function endpoint(): string { return 'https://api.aftership.com/v4/trackings'; }
}
