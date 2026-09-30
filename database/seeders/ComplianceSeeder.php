<?php

namespace Database\Seeders;

use App\Models\SanctionsEntry;
use App\Models\RestrictedItem;
use App\Models\HsCode;
use App\Models\VatRule;
use App\Models\ConsentTemplate;
use App\Models\KycVerification;
use Illuminate\Database\Seeder;

class ComplianceSeeder extends Seeder
{
    public function run()
    {
        if (SanctionsEntry::exists()) {
            return;
        }

        /* Sanctions sample entries */
        foreach ([
            ['Viktor Zakharov', 'RU', 'OFAC SDN'],
            ['Sergei Volkov', 'RU', 'EU Consolidated'],
            ['Nadia Petrova', 'BY', 'EU Consolidated'],
            ['Ahmed Al-Farsi', 'SY', 'UK Sanctions List'],
            ['Dmitri Ivanov', 'RU', 'OFAC SDN'],
            ['Carla Mendes', 'VE', 'EU Consolidated'],
        ] as [$name, $country, $list]) {
            SanctionsEntry::create(['full_name' => $name, 'country' => $country, 'list_name' => $list]);
        }

        /* Restricted items */
        $items = [
            ['Lithium batteries (standalone)', 'Electronics', 'restricted', 'Carriage regulations apply.', true],
            ['Perfumes (alcohol-based)', 'Cosmetics', 'restricted', 'Flammable liquid limits.', true],
            ['Aerosols', 'Household', 'restricted', 'Pressurized containers.', true],
            ['Live animals', 'Biological', 'prohibited', 'No live animal carriage.', false],
            ['Explosives / fireworks', 'Dangerous goods', 'prohibited', 'Class 1 dangerous goods.', false],
            ['Counterfeit goods', 'Legal', 'prohibited', 'IP infringement.', false],
            ['Narcotics', 'Legal', 'prohibited', 'Controlled substances.', false],
            ['Cash in currency', 'Financial', 'restricted', 'Declaration required above limits.', true],
            ['Dry ice', 'Dangerous goods', 'restricted', 'UN1845 packaging rules.', true],
            ['Human remains', 'Special', 'restricted', 'Special documentation.', true],
        ];
        foreach ($items as [$name, $cat, $sev, $reason, $decl]) {
            RestrictedItem::create([
                'name' => $name, 'category' => $cat, 'severity' => $sev,
                'reason' => $reason, 'requires_declaration' => $decl,
            ]);
        }

        /* HS codes */
        foreach ([
            ['4202.12', 'Trunks, suitcases of plastic/textile', 'Luggage', 8.50],
            ['6109.10', 'T-shirts, singlets, of cotton', 'Apparel', 12.00],
            ['8471.30', 'Portable data-processing machines (laptops)', 'Electronics', 0.00],
            ['8517.13', 'Smartphones', 'Electronics', 0.00],
            ['9403.20', 'Other metal furniture', 'Furniture', 4.70],
            ['3303.00', 'Perfumes and toilet waters', 'Cosmetics', 6.50],
            ['9506.91', 'Articles/equipment for general physical exercise', 'Sports', 4.10],
            ['7113.19', 'Jewellery of precious metal', 'Jewellery', 2.50],
        ] as [$code, $desc, $cat, $duty]) {
            HsCode::create(['code' => $code, 'description' => $desc, 'category' => $cat, 'duty_hint' => $duty]);
        }

        /* VAT rules */
        foreach ([
            ['eu_ioss', 'DE', 19.00, 'DE-IOSS-2026-001'],
            ['eu_ioss', 'FR', 20.00, 'FR-IOSS-2026-014'],
            ['uk_vat', 'GB', 20.00, 'GB-2026-777'],
            ['eu_b2b', 'NL', 21.00, null],
            ['standard', 'US', 0.00, null],
        ] as [$scheme, $cc, $rate, $reg]) {
            VatRule::create(['scheme' => $scheme, 'country_code' => $cc, 'rate' => $rate, 'registration_number' => $reg, 'is_active' => true]);
        }

        /* Consent templates */
        ConsentTemplate::create([
            'name' => 'Terms of Service', 'slug' => 'terms-of-service', 'version' => 1,
            'body' => "By using our services you agree to the forwarding terms, prohibited items policy and payment terms.",
            'is_active' => true, 'trigger_type' => 'always', 'requires_signature' => true,
        ]);
        ConsentTemplate::create([
            'name' => 'Lithium Battery Declaration', 'slug' => 'battery-declaration', 'version' => 1,
            'body' => "I confirm the lithium batteries in this shipment comply with UN38.3 and are properly packaged.",
            'is_active' => true, 'trigger_type' => 'cargo_type', 'trigger_value' => 'batteries', 'requires_signature' => true,
        ]);
        ConsentTemplate::create([
            'name' => 'IOSS VAT Consent (EU)', 'slug' => 'ioss-consent', 'version' => 1,
            'body' => "I confirm IOSS VAT arrangements for EU-bound goods as required by Article 369k of the VAT Directive.",
            'is_active' => true, 'trigger_type' => 'route', 'trigger_value' => 'EU', 'requires_signature' => false,
        ]);

        /* KYC samples referencing real users (first three ids) */
        $userIds = \DB::table('users')->orderBy('id')->limit(3)->pluck('id');
        foreach ($userIds as $i => $uid) {
            KycVerification::create([
                'user_id' => $uid, 'doc_type' => 'passport',
                'doc_number' => 'P' . (1000000 + $uid), 'doc_country' => 'US',
                'status' => $i === 0 ? 'pending' : ($i === 1 ? 'approved' : 'rejected'),
                'rejection_reason' => $i === 2 ? 'Document unreadable.' : null,
                'reviewed_by' => $i === 1 ? 1 : null,
                'reviewed_at' => $i === 1 ? now() : null,
            ]);
        }
    }
}
