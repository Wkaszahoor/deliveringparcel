<?php

namespace Database\Seeders\Mobile;

use App\Mobile\Models\MobileLabel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MobileLabelsSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('mobile_labels')) {
            return;
        }

        foreach (MobileLabel::defaults() as $key => $value) {
            MobileLabel::updateOrCreate(
                ['label_key' => $key],
                ['label_value' => $value]
            );
        }

        // Descriptions (kept here so the model stays lean)
        $descriptions = [
            'mobile_label_order'             => 'Top card: order info, client block, Progress/Chat/Edit buttons.',
            'mobile_label_shipping'          => 'From/to destination, address, weight, company.',
            'mobile_label_products'          => 'Inside Shipping Detail — customer items.',
            'mobile_label_tracking'          => 'Holds customer-provided and admin-provided tracking.',
            'mobile_label_tracking_customer' => 'Tracking the customer submits after payment.',
            'mobile_label_tracking_admin'    => 'Tracking when we ship to the customer destination.',
            'mobile_label_services'          => 'Services adopted by the customer on the request.',
            'mobile_label_status'            => 'Admin status chip grid.',
            'mobile_label_offer'             => 'Offer builder / breakdown card.',
            'mobile_label_chat'              => 'Inline chat card and chat room title.',
        ];
        foreach ($descriptions as $key => $desc) {
            MobileLabel::where('label_key', $key)->update(['description' => $desc]);
        }
    }
}
