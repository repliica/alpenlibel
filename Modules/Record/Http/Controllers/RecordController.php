<?php

namespace Modules\Record\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Record\Entities\Record;
use Modules\Record\Transformers\RecordTableResource;
use Modules\Record\Transformers\RecordTableResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordController extends Controller
{

    protected $user;

    function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        if (!$this->user->can('view-record')) {
            return $this->unauthorizedResponse([
                'errors' => ['Ora entuk ndes']
            ]);
        }

        $records = Record::paginate();
        return $this->okResponse([
            'data' => new RecordTableResourceCollection($records) 
        ]);
    }

    private function generateRefCode() {
        $num = implode("", Arr::shuffle([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]));
        return implode("-", ["RC",  $num, Str::random(4)]);   
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if (!$this->user->can('create-record')) {
            $this->unauthorizedResponse();
        }

        $this->validate($request, Record::REQ_RULES);

        $data = $request->only(array_keys(Record::REQ_RULES));
        $data['ref_code'] = $this->generateRefCode();

        DB::beginTransaction();
        $record = Record::create($data);

        if (!$record) {
            DB::rollback();
            return $this->unprocessableEntityResponse([
                'errors' => ['Unable to create new record']
            ]);
        }

        DB::commit();
        return $this->createdResponse([
            'data' => new RecordTableResource($record)
        ]);
    }

    public function storeBatch(Request $request) {
        if (!$this->user->can('create-record')) {
            return $this->unauthorizedResponse();
        }

        $this->validate($request, array_merge(
            Record::REQ_RULES,
            ['count' => 'required|numeric']
        ));

        $current_time = Carbon::now();
        $data = $request->only(array_keys(Record::REQ_RULES));
        $data['ref_code'] = $this->generateRefCode();
        $data['created_at'] = $current_time;
        $data['updated_at'] = $current_time;

        $count = intval($request->input('count') ?? 0);

        DB::beginTransaction();
        try {
            $records = [];
            for ($i=0; $i < $count; $i++) { 
                $records[$i] = Record::create($data);
            }

            if (count($records) !== $count) {
                DB::rollback();
                return $this->unprocessableEntityResponse([
                    'errors' => ['Something went wrong while trying to store some record']
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->unprocessableEntityResponse([
                'errors' => [$th->getMessage()]
            ]);
        }

        DB::commit();
        return $this->createdResponse([
            'data' => RecordTableResource::collection($records)
            // 'data' => new RecordTableResourceCollection($records)
        ]);
    }

    public function updateBatch(Request $request) {
        if (!$this->user->can('update-record')) {
            return $this->unauthorizedResponse();
        }

        $recordsId = $request->input('id') ?? [];
        if (is_string($recordsId)) {
            $recordsId = explode(",", $recordsId);
        }

        $refCodesByid = Record::whereIn('id', $recordsId)->pluck('ref_code')->toArray();
    
        $refCode = $request->input('ref_code');

        $useId = count($recordsId) > 0;
        $refCodesAreDifferent = count(array_unique($refCodesByid)) !== 1;

        $noIdOrRefCodeGiven = !$refCode && count($recordsId) <= 0;
        $useIdButTheRefCodeAreDifferent = $useId && $refCodesAreDifferent;

        if ($useId && !$refCode) {
            $refCode = $refCodesByid[0];
        } 

        if ($noIdOrRefCodeGiven || $useIdButTheRefCodeAreDifferent || !$refCode) {
            return $this->unprocessableEntityResponse([
                'errors' => ['Unable to process the request']
            ]);
        }

        $recordsByRefCode = Record::where('ref_code', $refCode)->count(); 
        $notAllSelected = $useId && !$refCode && intval($recordsByRefCode) !== count($recordsId);

        if (!$recordsByRefCode || $notAllSelected) {
            return $this->unprocessableEntityResponse([
                'errors' => ['Unable to process the request']
            ]);
        } 

        $this->validate($request, Record::REQ_RULES);
        $data = $request->only(array_keys(Record::REQ_RULES));

        DB::beginTransaction();
        if (!Record::where('ref_code', $refCode)->update($data)) {
            DB::rollback();
            return $this->unprocessableEntityResponse([
                'errors' => ['Something went wrong while trying to update a record']
            ]);
        }

        DB::commit();
        return $this->okResponse();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        if (!$this->user->can('view-record')) {
            return $this->unauthorizedResponse();
        }

        if (!$record = Record::find($id)) {
            return $this->unprocessableEntityResponse([
                'errors' => ['Can\'t find record with id '.$id]
            ]);
        }

        return $this->okResponse([
            'data' => new RecordTableResource($record)
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->user->can('update-record')) {
            return $this->unauthorizedResponse();
        }

        if (!$record = Record::find($id)) {
            return $this->unprocessableEntityResponse([
                'errors' => ['Can\'t find record with id '.$id]
            ]);
        }

        $this->validate($request, Record::REQ_RULES);

        $data = $request->only(array_keys(Record::REQ_RULES));
        DB::beginTransaction();
        if (!$record->update($data)) {
            DB::rollback();
            return $this->unprocessableEntityResponse([
                'errors' => ['Something went wrong while trying to update a record']
            ]);
        }

        DB::commit();
        return $this->okResponse(['data' => new RecordTableResource($record)]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
