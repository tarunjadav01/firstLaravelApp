<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessEvent;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $payload = $request->input('payload');
        if (!$payload) {
            return response()->json(['error' => 'Payload missing'], 400);
        }

        $decoded = json_decode(base64_decode($payload), true);

        if (!$decoded) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $required = ['session_id', 'event_type', 'timestamp'];
        foreach ($required as $field) {
            if (empty($decoded[$field])) {
                return response()->json(['error' => "Missing $field"], 400);
            }
        }

        // Get tenant_id from JWT
        $tenantId = $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['error' => 'Tenant ID missing'], 400);
        }
        $decoded['tenant_id'] = $tenantId;
        // Push to queue (async)
        ProcessEvent::dispatch($decoded);

        return response()->json(['status' => 'accepted']);
    }
}
