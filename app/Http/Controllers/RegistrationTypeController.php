<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegistrationTypeController extends Controller
{
    public function getRegistrationTypes() {
        $registration_type = RegistrationType::all();

        if($registration_type->isNotEmpty()) {
            return response()->json($registration_type);
        } else {
            return response()->json(['message' => 'No registration types found'], 404);
        }
    }
}