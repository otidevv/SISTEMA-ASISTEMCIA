<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\BiometricDevice;
use Illuminate\Support\Facades\DB;

class BiometricApiController extends BaseController
{
    /**
     * List all synchronized biometric devices
     */
    public function index()
    {
        $devices = BiometricDevice::all();
        return $this->sendResponse($devices, 'Dispositivos recuperados.');
    }

    /**
     * Enroll a user in a device
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_id' => 'required|exists:biometric_devices,id',
        ]);

        // Logic here to send command to device
        return $this->sendResponse([], 'Comando de enrolamiento enviado.');
    }

    /**
     * Check status of a command
     */
    public function checkCommand($id)
    {
        // Check command table
        return $this->sendResponse(['status' => 'pending'], 'Estado del comando.');
    }

    /**
     * Sync users with device
     */
    public function sync(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:biometric_devices,id',
        ]);

        return $this->sendResponse([], 'Sincronización iniciada.');
    }
}
