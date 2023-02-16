<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\ConventionMember;
use App\Models\Abstracts;
use App\Models\AbstractAuthor;
use App\Models\AbstractCategory;
use App\Models\AbstractStudyDesign;

use App\Enum\AbstractTypeEnum;
use App\Enum\RoleEnum;

use App\Http\Requests\Abstracts\Create;

use Exception;
use DB;

class AbstractController extends Controller
{
    public function create(Create $request) {
        $validated = $request->validated();
        $user_id = Auth::user()->id;

        $member = ConventionMember::with(['user'])
            ->where('user_id', $user_id)
            ->delegates()
            ->first();

        if(is_null($member)) {
            return response()->json(['message' => 'You are not allowed to submit an abstract.'], 404);
        }

        DB::beginTransaction();
        try {
            $validated['convention_member_id'] = $member->id;
            $abstract = Abstracts::create($validated);

            foreach ($validated['authors'] as $author){
                $author['abstract_id'] = $abstract->id;
                $abstract_authotr = AbstractAuthor::create($author);
            }

            DB::commit();
            Abstracts::sendThankYouEmail($abstract->member->user, $abstract);
            return response()->json(['message' => 'Successfully submitted your abstract.']);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getUserAbstracts(Request $request) {
        $abstracts = Abstracts::where('convention_member_id', $request->member_id)
            ->with(['member'])
            ->get();

        if($abstracts->isNotEmpty()) {
            return response()->json($abstracts);
        } else {
            return response()->json(['message' => 'This member has no abstract submissions'], 404);
        }
    }

    public function getAbstract($id) {
        $abstract = Abstracts::where('id',$id)->with(['authors'])->first();

        if(!is_null($abstract)) {
            return response()->json($abstract);
        } else {
            return response()->json(['message' => 'This abstract submission does not exist.'], 404);
        }
    }

    public function getEPosterAbstracts() {
        $abstracts = Abstracts::where('abstract_type', AbstractTypeEnum::E_POSTER)
            ->with(['member', 'authors' ])
            ->orderBy('id', 'desc')
            ->get();

        if($abstracts->isNotEmpty()) {
            return response()->json($abstracts);
        } else {
            return response()->json(['message' => 'No abstract submissions found'], 404);
        }
    }

    public function getFreePaperAbstracts() {
        $abstracts = Abstracts::where('abstract_type', AbstractTypeEnum::FREE_PAPER)
            ->with(['member', 'authors'])
            ->orderBy('id', 'desc')
            ->get();

        if($abstracts->isNotEmpty()) {
            return response()->json($abstracts);
        } else {
            return response()->json(['message' => 'No abstract submissions found'], 404);
        }
    }

    public function resendThankYouEmail($id) {
        $abstract_submission = Abstracts::where('id', $id)->first();
        if(!is_null($abstract_submission)) {
            $status = Abstracts::sendThankYouEmail($abstract_submission->member->user, $abstract_submission);

            if($status == 200) {
                return response()->json([
                    'message' => 'Successfully resent the email receipt of the abstract content to the Delegate.',
                    'email' => $abstract_submission->member->user->email,
                    'status' => $status,
                ]);
            } else {
                return response()->json([
                    'message' => 'Unable to resent the thank you email.',
                    'status' => $status
                ], 400);
            }
        } else {
            return response()->json([
                'message' => 'The abstract submission was not found.'
            ], 404);
        }
    }
}
