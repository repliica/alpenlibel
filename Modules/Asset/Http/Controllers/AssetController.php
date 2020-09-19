<?php

namespace Modules\Asset\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Asset\Entities\Asset;
use Modules\Asset\Transformers\AssetTableResourceCollection;
use Modules\Asset\Transformers\AssetTableResource;
use Modules\Category\Entities\Category;

class AssetController extends Controller
{
    #create : create-asset
    #view list : view-asset
    #show: view-asset
    #delete : delete-asset
    #update category: update-asset

    /**
     * Display a listing of the resource.
     * @return Response
     */

    protected $user = null;
    
    public function __construct(){
        $this->user = auth()->user();        
    }

    public function index()
    {
        if($this->user->can('view-asset')){
            $assets = Asset::paginate();
            return response()->json(
                [
                    'success'   =>true,
                    'message'   =>'successfully retrieve some asset data',
                    'data'      => new AssetTableResourceCollection($assets)
                ], 200
            );
        }

        return response()->json(
            [
                'success'   =>false,
                'message'   =>'Unauthorized',
                'errors'    => 'You\'re prohibited to access this resource'
            ], 401
        );
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('asset::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if($this->user->can('create-asset')){
            $this->validate($request, Asset::REQ_RULES);

            #create asset
            $assetData = $request->only(array_keys(Asset::REQ_RULES));
            $asset = new Asset;
            $asset->name = $assetData['name'];
            $asset->description = $assetData['description'];
            $asset->category_id = $assetData['category_id'];
            
            #if image not null
            if ($request->hasFile('image')) {
                $assetImage = $request->file('image');
                $dirPath = storage_path(Asset::ASSET_DIR);

                if (!is_dir($dirPath)) { //check if directory doesn't exist
                    Storage::makeDirectory(Asset::ASSET_DIR); //make new directory
                }

                $fileExt = $assetImage->getClientOriginalExtension(); //get file ext
                $fileName = implode('-', [
                    'asset',
                    $assetData['name'],
                    time()
                ]);

                #store image
                $savedFile = Storage::putFileAs(
                    Asset::ASSET_DIR, //folder
                    $assetImage, //file content
                    implode(".", [$fileName, $fileExt]) //filename
                );

                if (!Storage::exists($savedFile)) { 
                    return $this->unprocessableEntityResponse([
                        'message' => "Unable to store the image"
                    ]);
                }

                $asset->image = "storage/".$savedFile;
            }

            #store asset data
            if($asset->save()){
                return response()->json([
                    'success'   => true,
                    'message'   => 'Created',
                    'data'      => new AssetTableResource($asset)
                ], 201);
            }

            return response()->json([
                'success'   => false,
                'message'   => 'Unprocesable entity',
                'errors'      => [
                    'Unable to create new record'
                ]
            ], 422);
        }
        
        return response()->json(
            [
                'success'   =>false,
                'message'   =>'Unauthorized',
                'errors'    => 'You\'re prohibited to access this resource'
            ], 401
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        if($this->user->can('view-asset')){
            $asset = Asset::find($id);
            if(!$asset){
                return response()->json([
                    "message" => "unprocessable entity",
                    "errors"    => [
                        "Can't find record with id ".$id
                    ]
                ], 422);
            }
            return response()->json([
                "success" => true,
                "message" => "successfully retrieve asset data",
                "data" => new AssetTableResource($asset),
            ]);
        } //end of if permission
        return response()->json(
            [
                'success'   =>false,
                'message'   =>'Unauthorized',
                'errors'    => 'You\'re prohibited to access this resource'
            ], 401
        );
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('asset::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if($this->user->can('update-asset')){
            $this->validate($request, Asset::REQ_RULES);

            #update asset
            $data = $request->only('name','description','category_id');

            $asset = Asset::find($id);
            if(!$asset){
                return response()->json([
                    "message" => "unprocessable entity",
                    "errors"    => [
                        "Can't find record with id ".$id
                    ]
                ], 422);
            }

            if (!Category::find($data['category_id'])) {
                return response()->json([
                    'success'   => false,
                    'message'   => "Unprocessable Entry",
                    'errors'    => ["Can't update asset"],
                ], 422);
            }
            
            #if image not null
            if ($request->hasFile('image')) {
                $assetImage = $request->file('image');
                $dirPath = storage_path(Asset::ASSET_DIR);

                if (!is_dir($dirPath)) { //check if directory doesn't exist
                    Storage::makeDirectory(Asset::ASSET_DIR); //make new directory
                }

                $fileExt = $assetImage->getClientOriginalExtension(); //get file ext
                $fileName = implode('-', [
                    'asset',
                    $data['name'],
                    time()
                ]);

                #store image
                $savedFile = Storage::putFileAs(
                    Asset::ASSET_DIR, //folder
                    $assetImage, //file content
                    implode(".", [$fileName, $fileExt]) //filename
                );

                if (!Storage::exists($savedFile)) { 
                    return $this->unprocessableEntityResponse([
                        'message' => "Unable to store the image"
                    ]);
                }
                
                $oldFile = $asset->image;
                $asset->image = $savedFile;
            }

            if ($asset->update($data)) {
                if (Storage::exists($oldFile)) {
                    Storage::delete($oldFile);
                }

                return response()->json([
                    "success" => true,
                    "message" => "Asset ".$asset->id." has been updated",
                    "data"    => new AssetTableResource($asset)
                ]); 
            }
            return response()->json([
                'success'   => false,
                'message'   => "Unprocessable Entry",
                'errors'    => ["Can't update asset"],
            ], 222);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $asset = Asset::find($id);
        if(!$asset){
            return response()->json([
                "message" => "unprocessable entity",
                "errors"    => [
                    "Can't find record with id ".$id
                ]
            ], 422);
        }

        $asset->delete();
        return response()->json([
            "status"  => true,
            "message" => "Delete success",
            "data"    => $asset
        ]);
    }
}
