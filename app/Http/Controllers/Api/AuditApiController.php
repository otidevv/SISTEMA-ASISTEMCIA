<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditApiController extends BaseController
{
    /**
     * Get system activity logs for administrators
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 50);
        $userId = $request->input('user_id');

        $query = Activity::with('causer')->orderBy('created_at', 'desc');

        if ($userId) {
            $query->where('causer_id', $userId);
        }

        $activities = $query->paginate($limit);

        return $this->sendResponse($activities, 'Logs de auditoría recuperados.');
    }
}
