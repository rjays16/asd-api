<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Fee;
use App\Models\FeeType;

use App\Http\Requests\Fees\Create;

use Exception;
use DB;

class FeeController extends Controller
{
    public function getFees() {
        $fees = Fee::all();

        if($fees->isNotEmpty()) {
            return response()->json($fees);
        } else {
            return response()->json(['message' => 'No fees were found'], 404);
        }
    }

    public function getFee($id) {
        $fee = Fee::where('id', $id)->first();

        if(!is_null($fee)) {
            return response()->json($fee);
        } else {
            return response()->json(['message' => 'Fee not found'], 404);
        }
    }

    public function create(Create $request) {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            Fee::create($validated);

            DB::commit();
            return response()->json([
                'message' => 'Successfully created fee'
            ]);
        } catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Create $request, $id) {
        $validated = $request->validated();

        $fee = Fee::where('id', $id)->first();
        if(is_null($fee)) {
            return response()->json(['message' => 'Fee not found'], 404);
        }

        DB::beginTransaction();
        try {
            $fee->update($validated);

            DB::commit();
            return response()->json([
                'message' => 'Successfully updated fee'
            ]);
        } catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $fee = Fee::where('id', $id)->first();

        if(!is_null($fee)) {
            $fee->delete();
            return response()->json(['message' => 'Fee deleted']);
        } else {
            return response()->json(['message' => 'Fee not found'], 404);
        }
    }
}