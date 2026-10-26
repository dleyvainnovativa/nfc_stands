<?php

namespace App\Http\Controllers;

use App\Models\GooglePlace;
use App\Models\Stand;
use App\Models\StandDailyStat;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $platformView = $user->isSuperAdmin() && ! CurrentTenant::isActingAs();
        $since = Carbon::today()->subDays(29)->toDateString();

        if ($platformView) {
            $scans30 = StandDailyStat::where('stat_date', '>=', $since)->sum('scan_count');
            $stats = [
                ['label' => 'Clientes',    'value' => Tenant::count(),                        'icon' => 'fa-building'],
                ['label' => 'Stands',      'value' => Stand::count(),                         'icon' => 'fa-wifi'],
                ['label' => 'Escaneos (30d)', 'value' => number_format($scans30),             'icon' => 'fa-hand-pointer'],
                ['label' => 'Etiquetas en blanco', 'value' => Stand::whereNull('tenant_id')->count(), 'icon' => 'fa-tags'],
            ];
            $scope = 'Plataforma';
        } else {
            $scans30 = StandDailyStat::where('stat_date', '>=', $since)->sum('scan_count');
            $avgRating = GooglePlace::whereNotNull('rating')->avg('rating');

            $stats = [
                ['label' => 'Stands',      'value' => Stand::count(),                                       'icon' => 'fa-wifi'],
                ['label' => 'Activos',     'value' => Stand::where('status', Stand::STATUS_ACTIVE)->count(), 'icon' => 'fa-circle-check'],
                ['label' => 'Escaneos (30d)', 'value' => number_format($scans30),                           'icon' => 'fa-hand-pointer'],
                ['label' => 'Calif. promedio', 'value' => $avgRating ? number_format($avgRating, 1) : '—',  'icon' => 'fa-star'],
            ];
            $scope = CurrentTenant::model()?->name ?? 'Tu cuenta';
        }

        return view('dashboard.index', compact('stats', 'scope', 'platformView'));
    }
}
