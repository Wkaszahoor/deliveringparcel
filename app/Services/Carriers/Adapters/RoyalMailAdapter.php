<?php

namespace App\Services\Carriers\Adapters;

use App\Services\Carriers\BaseCarrierAdapter;

class RoyalMailAdapter extends BaseCarrierAdapter
{
    public function code(): string { return 'royalmail'; }
    public function name(): string { return 'Royal Mail'; }
    protected function envKeys(): array { return ['CARRIER_ROYALMAIL_KEY']; }
    protected function endpoint(): string { return 'https://api.royalmail.net/tracking'; }
}
