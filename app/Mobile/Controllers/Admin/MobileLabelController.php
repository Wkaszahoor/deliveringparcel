<?php

namespace App\Mobile\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mobile\Models\MobileLabel;
use App\Mobile\Requests\UpdateLabelsRequest;
use App\Mobile\Services\MobileSettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MobileLabelController extends Controller
{
    public function index()
    {
        $labels = MobileLabel::orderBy('id')->get();

        return view('mobile-admin.labels.index', compact('labels'));
    }

    public function update(UpdateLabelsRequest $request)
    {
        foreach ($request->labels as $item) {
            MobileLabel::where('label_key', $item['label_key'])
                ->update(['label_value' => $item['label_value']]);
        }

        $this->mirrorToSettings($request->labels);
        app(MobileSettingsService::class)->clearAllCaches();

        return back()->with('success', 'Labels saved — the app picks them up on next refresh.');
    }

    public function reset()
    {
        MobileLabel::resetToDefaults();
        $this->mirrorToSettings(
            collect(MobileLabel::defaults())->map(fn ($v, $k) => ['label_key' => $k, 'label_value' => $v])->all()
        );
        app(MobileSettingsService::class)->clearAllCaches();

        return back()->with('success', 'Labels reset to defaults.');
    }

    /**
     * Keep the LIVE /api/mobile/labels endpoint (settings-table based,
     * read by the current app build) in sync with this table — done via
     * DB writes so the mobile module stays independent of web models.
     */
    private function mirrorToSettings(array $labels): void
    {
        foreach ($labels as $item) {
            DB::table('settings')->updateOrInsert(
                ['key' => $item['label_key']],
                [
                    'value'      => $item['label_value'],
                    'group'      => 'mobile',
                    'type'       => 'string',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        Cache::forget('dp_settings_all');
    }
}
