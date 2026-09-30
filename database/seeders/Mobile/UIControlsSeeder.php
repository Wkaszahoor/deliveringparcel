<?php

namespace Database\Seeders\Mobile;

use App\Mobile\Models\MobileUiAction;
use App\Mobile\Models\MobileUiElement;
use App\Mobile\Models\MobileUiScreen;
use App\Mobile\Models\MobileUiSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the full 4-level UI control hierarchy with the default
 * visibility matrix (admin/client V+E per level, status rules for actions).
 * Idempotent via updateOrCreate.
 */
class UIControlsSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('mobile_ui_screens')) {
            return;
        }

        $this->seedScreens();
        $this->seedSections();
        $this->seedElements();
        $this->seedActions();
    }

    /* [key, name, parent, order, global_status, adminV, adminE, clientV, clientE, icon] */
    private function seedScreens(): void
    {
        $rows = [
            ['home',           'Home',           null,       1, 'active',   1, 1, 1, 1, 'home'],
            ['orders',         'Orders',         null,       2, 'active',   1, 1, 1, 1, 'list-alt'],
            ['order_detail',   'Order Detail',   null,       3, 'active',   1, 1, 1, 1, 'receipt-long'],
            ['order_tracking', 'Order Tracking', null,       4, 'active',   1, 1, 0, 0, 'local-shipping'],
            ['messages',       'Messages',       null,       5, 'active',   1, 1, 1, 1, 'chat'],
            ['chat_room',      'Chat Room',      'messages', 1, 'active',   1, 1, 1, 1, 'forum'],
            ['notifications',  'Notifications',  null,       6, 'active',   1, 1, 1, 1, 'notifications'],
            ['profile',        'Profile',        null,       7, 'active',   1, 1, 1, 1, 'person'],
            ['edit_profile',   'Edit Profile',   'profile',  1, 'active',   1, 1, 1, 1, 'edit'],
            ['settings',       'Settings',       null,       8, 'disabled', 1, 1, 0, 0, 'settings'],
        ];

        foreach ($rows as [$key, $name, $parent, $order, $status, $aV, $aE, $cV, $cE, $icon]) {
            MobileUiScreen::updateOrCreate(['screen_key' => $key], [
                'screen_name' => $name, 'parent_key' => $parent, 'screen_order' => $order,
                'global_status' => $status,
                'admin_visible' => $aV, 'admin_enabled' => $aE,
                'client_visible' => $cV, 'client_enabled' => $cE,
                'icon' => $icon,
            ]);
        }
    }

    /* [screenKey, key, name, order, aV, aE, cV, cE] — default all-true unless noted. */
    private function seedSections(): void
    {
        $rows = [
            // order_detail
            ['order_detail', 'flow_banner',      'Order Flow Banner',             0, 1, 1, 1, 1],
            ['order_detail', 'order_info',      'Order Details',    1, 1, 1, 1, 1],
            ['order_detail', 'client_details',  'Client Details',   2, 1, 1, 1, 0],
            ['order_detail', 'shipping_detail', 'Shipping Detail',  3, 1, 1, 1, 1],
            ['order_detail', 'product_list',    'Product List',     4, 1, 1, 1, 0],
            ['order_detail', 'tracking',        'Tracking',         5, 1, 1, 1, 0],
            ['order_detail', 'services',        'Services',         6, 1, 1, 1, 0],
            ['order_detail', 'change_status',   'Change Status',    7, 1, 1, 0, 0],
            ['order_detail', 'offer',           'Offer',            8, 1, 1, 1, 1],
            ['order_detail', 'payments',        'Payments',         9, 1, 1, 1, 1],
            ['order_detail', 'inline_chat',     'Chat',            10, 1, 1, 1, 1],
            ['order_detail', 'package_workflow', 'Package & Dispatch Workflow', 9, 1, 1, 0, 0],
            // orders
            ['orders', 'orders_header',  'Orders Header', 1, 1, 1, 1, 1],
            ['orders', 'orders_filters', 'Filter Bar',    2, 1, 1, 1, 1],
            ['orders', 'orders_list',    'Orders List',   3, 1, 1, 1, 1],
            ['orders', 'orders_search',  'Search',        4, 1, 1, 1, 1],
            // messages
            ['messages', 'messages_search',    'Search Bar',        1, 1, 1, 1, 1],
            ['messages', 'messages_filters',   'Filter Chips',      2, 1, 1, 1, 1],
            ['messages', 'conversations_list', 'Conversation List', 3, 1, 1, 1, 1],
            // notifications
            ['notifications', 'notif_header',  'Header',             1, 1, 1, 1, 1],
            ['notifications', 'notif_filters', 'Filter Tabs',        2, 1, 1, 1, 1],
            ['notifications', 'notif_list',    'Notifications List', 3, 1, 1, 1, 1],
            // profile
            ['profile', 'profile_info',     'Profile Information', 1, 1, 1, 1, 1],
            ['profile', 'profile_stats',    'Order Statistics',    2, 1, 1, 1, 1],
            ['profile', 'profile_settings', 'App Settings',        3, 1, 1, 1, 1],
        ];

        foreach ($rows as [$screen, $key, $name, $order, $aV, $aE, $cV, $cE]) {
            MobileUiSection::updateOrCreate(['section_key' => $key], [
                'screen_key' => $screen, 'section_name' => $name, 'section_order' => $order,
                'global_status' => 'active',
                'admin_visible' => $aV, 'admin_enabled' => $aE,
                'client_visible' => $cV, 'client_enabled' => $cE,
            ]);
        }
    }

    /* [sectionKey, key, name, type, order, aV, aE, cV, cE] */
    private function seedElements(): void
    {
        $rows = [
            // order_info
            ['order_info', 'order_id_display',  'Order Number',   'text', 1, 1, 1, 1, 1],
            ['order_info', 'order_status_pill', 'Status Badge',   'pill', 2, 1, 1, 1, 1],
            ['order_info', 'order_date',        'Order Date',     'text', 3, 1, 1, 1, 1],
            ['order_info', 'order_type_badge',  'Order Type',     'badge', 4, 1, 1, 1, 1],
            // client_details
            ['client_details', 'client_avatar', 'Client Avatar', 'image', 1, 1, 1, 1, 1],
            ['client_details', 'client_name',   'Client Name',   'text',  2, 1, 1, 1, 1],
            ['client_details', 'client_email',  'Client Email',  'link',  3, 1, 1, 1, 1],
            ['client_details', 'client_phone',  'Client Phone',  'link',  4, 1, 1, 1, 1],
            // shipping_detail — spec matrix
            ['shipping_detail', 'ship_from',      'Ship From',        'text', 1, 1, 1, 1, 0],
            ['shipping_detail', 'ship_to',        'Ship To',          'text', 2, 1, 1, 1, 0],
            ['shipping_detail', 'recipient_name', 'Recipient Name',   'text', 3, 1, 1, 1, 1],
            ['shipping_detail', 'recipient_phone','Recipient Phone',  'text', 4, 1, 1, 1, 1],
            ['shipping_detail', 'address_line1',  'Address Line 1',   'text', 5, 1, 1, 1, 1],
            ['shipping_detail', 'address_line2',  'Address Line 2',   'text', 6, 1, 1, 1, 1],
            ['shipping_detail', 'city',           'City',             'text', 7, 1, 1, 1, 1],
            ['shipping_detail', 'state',          'State/Province',   'text', 8, 1, 1, 1, 1],
            ['shipping_detail', 'postal_code',    'Postal Code',      'text', 9, 1, 1, 1, 1],
            ['shipping_detail', 'country',        'Country',          'text', 10, 1, 1, 1, 1],
            ['shipping_detail', 'approx_weight',  'Approx. Weight',   'text', 11, 1, 1, 1, 0],
            ['shipping_detail', 'company_name',   'Company Name',     'text', 12, 1, 1, 1, 0],
            // product_list
            ['product_list', 'product_image', 'Product Image', 'image', 1, 1, 1, 1, 1],
            ['product_list', 'product_name',  'Product Name',  'text',  2, 1, 1, 1, 1],
            ['product_list', 'product_url',   'Product URL',   'link',  3, 1, 1, 1, 1],
            ['product_list', 'product_qty',   'Quantity',      'text',  4, 1, 1, 1, 1],
            ['product_list', 'product_price', 'Price',         'text',  5, 1, 1, 1, 1],
            ['product_list', 'product_weight','Weight',        'text',  6, 1, 1, 1, 1],
            ['product_list', 'receipt_link',  'Receipt Link',  'link',  7, 1, 1, 1, 1],
            // tracking
            ['tracking', 'customer_tracking', 'Customer Tracking', 'text', 1, 1, 1, 1, 1],
            ['tracking', 'admin_tracking',    'Admin Tracking',    'text', 2, 1, 1, 1, 1],
            ['tracking', 'tracking_id',       'Tracking ID',       'text', 3, 1, 1, 1, 1],
            ['tracking', 'tracking_link',     'Tracking Link',     'link', 4, 1, 1, 1, 1],
            ['tracking', 'tracking_status',   'Tracking Status',   'pill', 5, 1, 1, 1, 1],
            // offer
            ['offer', 'offer_amount',      'Offer Amount',     'text', 1, 1, 1, 1, 1],
            ['offer', 'offer_description', 'Description',      'text', 2, 1, 1, 1, 1],
            ['offer', 'product_charges',   'Product Charges',  'text', 3, 1, 1, 1, 1],
            ['offer', 'service_charges',   'Service Charges',  'text', 4, 1, 1, 1, 1],
            ['offer', 'shipping_charges',  'Shipping Charges', 'text', 5, 1, 1, 1, 1],
            ['offer', 'offer_total',       'Total',            'text', 6, 1, 1, 1, 1],
            ['offer', 'offer_history',     'Offer History',    'link', 7, 1, 1, 1, 1],
            // payments
            ['payments', 'payment_amount',    'Payment Amount',    'text', 1, 1, 1, 1, 1],
            ['payments', 'payment_method',    'Payment Method',    'text', 2, 1, 1, 1, 1],
            ['payments', 'payment_status',    'Payment Status',    'pill', 3, 1, 1, 1, 1],
            ['payments', 'payment_date',      'Payment Date',      'text', 4, 1, 1, 1, 1],
            ['payments', 'payment_reference', 'Reference Number',  'text', 5, 1, 1, 1, 1],
            ['payments', 'payment_receipt',   'Payment Receipt',   'image', 6, 1, 1, 1, 1],
            // package_workflow — admin-only dispatch workflow (spec matrix)
            ['package_workflow', 'b1_client_tracking',     'Client Tracking Review',    'text', 1, 1, 1, 0, 0],
            ['package_workflow', 'b2_package_received',    'Package Received + Photos', 'text', 2, 1, 1, 0, 0],
            ['package_workflow', 'b3_dispatch_tracking',   'Dispatch Tracking Form',    'text', 3, 1, 1, 0, 0],
            ['package_workflow', 'b4_customs_declaration', 'Customs Declaration',       'text', 4, 1, 1, 0, 0],
        ];

        foreach ($rows as [$section, $key, $name, $type, $order, $aV, $aE, $cV, $cE]) {
            MobileUiElement::updateOrCreate(['element_key' => $key], [
                'section_key' => $section, 'element_name' => $name, 'element_type' => $type,
                'element_order' => $order, 'global_status' => 'active',
                'admin_visible' => $aV, 'admin_enabled' => $aE,
                'client_visible' => $cV, 'client_enabled' => $cE,
            ]);
        }
    }

    /* [section, key, name, label, type, order, aV, aE, cV, cE, statuses, confirm, msg, icon, color] */
    private function seedActions(): void
    {
        $rows = [
            // order_info
            ['order_info', 'btn_progress',     'Progress Timeline', 'Progress',       'navigate', 1, 1, 1, 1, 1, null, 0, null, 'timeline', '#1565C0'],
            ['order_info', 'btn_chat',         'Open Chat',         'Chat',           'navigate', 2, 1, 1, 1, 1, null, 0, null, 'chat', '#0288D1'],
            ['order_info', 'btn_edit_details', 'Edit Details',      'Edit Details',   'modal',    3, 1, 1, 0, 0, null, 0, null, 'edit', '#3c8dbc'],
            // shipping_detail
            ['shipping_detail', 'btn_email_client', 'Email Client', 'Email', 'link', 1, 1, 1, 1, 1, null, 0, null, 'mail', '#1565C0'],
            ['shipping_detail', 'btn_call_client',  'Call Client',  'Call',  'link', 2, 1, 1, 1, 1, null, 0, null, 'phone', '#43A047'],
            // tracking
            ['tracking', 'btn_open_tracking', 'Open Tracking Link', 'Open Link', 'link', 1, 1, 1, 1, 1, null, 0, null, 'open-in-new', '#7B1FA2'],
            // change_status
            ['change_status', 'btn_fix_offer', 'Fix Stuck Offer Status', 'Fix Offer', 'api_call', 1, 1, 1, 0, 0, null, 1, 'Re-align the offer status with the order status?', 'healing', '#F57C00'],
            // offer — spec matrix
            ['offer', 'btn_create_offer',  'Create Offer',  'New Offer',    'modal',    1, 1, 1, 0, 0, null, 0, null, 'add-circle', '#43A047'],
            ['offer', 'btn_edit_offer',    'Edit Offer',    'Edit Offer',   'modal',    2, 1, 1, 0, 0, null, 0, null, 'edit', '#3c8dbc'],
            ['offer', 'btn_close_offer',   'Close Offer Builder', 'Close',  'modal',    3, 1, 1, 0, 0, null, 0, null, 'close', '#9E9E9E'],
            ['offer', 'btn_accept_offer',  'Accept Offer',  'Accept Offer', 'api_call', 4, 0, 0, 1, 1, ['pending', 'offer_placed', 'Offer Placed', 'Offer Updated'], 1, 'Accept this offer?', 'thumb-up', '#43A047'],
            ['offer', 'btn_reject_offer',  'Reject Offer',  'Reject Offer', 'api_call', 5, 0, 0, 1, 1, ['pending', 'offer_placed', 'Offer Placed', 'Offer Updated'], 1, 'Reject this offer?', 'thumb-down', '#E53935'],
            ['offer', 'btn_pay_now',       'Pay Now',       'Pay Now',      'navigate', 6, 1, 1, 1, 1, ['accepted', 'Offer Accepted'], 0, null, 'payments', '#F57C00'],
            ['offer', 'btn_view_offer_history', 'View Offer History', 'History', 'modal', 7, 1, 1, 1, 1, null, 0, null, 'history', '#9E9E9E'],
            // payments — spec matrix
            ['payments', 'btn_approve_payment', 'Approve Payment', 'Approve',   'api_call', 1, 1, 1, 0, 0, ['awaiting_verification', 'awaiting_payment'], 1, 'Confirm payment received?', 'check-circle', '#43A047'],
            ['payments', 'btn_reject_payment',  'Reject Payment',  'Reject',    'api_call', 2, 1, 1, 0, 0, ['awaiting_verification'], 1, 'Reject this payment receipt?', 'cancel', '#E53935'],
            ['payments', 'btn_mark_received',   'Mark Received',   'Mark Received', 'api_call', 3, 1, 1, 0, 0, ['awaiting_payment'], 0, null, 'mark-chat-read', '#00897B'],
            ['payments', 'btn_view_receipt',    'View Receipt',    'Receipt',   'modal',    4, 1, 1, 1, 1, null, 0, null, 'receipt', '#1565C0'],
            // inline_chat
            ['inline_chat', 'btn_send_message',   'Send Message',     'Send',     'api_call', 1, 1, 1, 1, 1, null, 0, null, 'send', '#1565C0'],
            ['inline_chat', 'btn_open_full_chat', 'Open Full Chat',   'Full Chat','navigate', 2, 1, 1, 1, 1, null, 0, null, 'open-in-new', '#0288D1'],
            // package_workflow — admin-only dispatch workflow
            ['package_workflow', 'btn_submit_package_received',  'Submit Package Received', 'Submit Package Received', 'api_call', 1, 1, 1, 0, 0, null, 0, null, 'inventory', '#00897B'],
            ['package_workflow', 'btn_submit_dispatch_tracking', 'Submit Dispatch Tracking', 'Submit Tracking',         'api_call', 2, 1, 1, 0, 0, null, 0, null, 'local-shipping', '#1565C0'],
        ];

        // Dynamic status chips: btn_status_<slug> per legacy order status.
        foreach (config('mobile.order_status_labels', []) as $raw => $label) {
            $slug = str_replace(' ', '_', strtolower($raw !== '' ? $raw : 'request_placed'));
            $rows[] = ['change_status', 'btn_status_' . $slug, 'Set Status: ' . $label, $label, 'api_call', 10, 1, 1, 0, 0, null, 1, "Change status to \"{$label}\"?", 'label', '#3c8dbc'];
        }

        foreach ($rows as [$section, $key, $name, $label, $type, $order, $aV, $aE, $cV, $cE, $statuses, $confirm, $msg, $icon, $color]) {
            MobileUiAction::updateOrCreate(['action_key' => $key], [
                'section_key' => $section, 'action_name' => $name, 'action_label' => $label,
                'action_type' => $type, 'action_order' => $order, 'global_status' => 'active',
                'admin_visible' => $aV, 'admin_enabled' => $aE,
                'client_visible' => $cV, 'client_enabled' => $cE,
                'allowed_order_statuses' => $statuses,
                'confirmation_required' => $confirm,
                'confirmation_message' => $msg,
                'icon' => $icon, 'color' => $color,
            ]);
        }
    }
}
