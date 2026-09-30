<?php

namespace App\Services\Carriers\Adapters;

use App\Services\Carriers\BaseCarrierAdapter;

class SeventeenTrackAdapter extends BaseCarrierAdapter
{
    public function code(): string { return '17track'; }
    public function name(): string { return '17TRACK'; }
    protected function envKeys(): array { return ['CARRIER_17TRACK_KEY']; }
    protected function endpoint(): string { return 'https://api.17track.net/track/v2.2/register'; }
}
