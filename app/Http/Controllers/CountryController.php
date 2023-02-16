<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Country;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{
    public function getCountries() {
        $country = Country::all();

        if(!is_null($country)) {
            return response()->json($country);
        } else {
            return response()->json(['message' => 'The data for the countries have not been set yet'], 404);
        }
    }

}
