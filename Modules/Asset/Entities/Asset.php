<?php

namespace Modules\Asset\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    const REQ_RULES = [
        'name'          => 'required|string|max:100',
        'description'   => 'required|max:255',
        'category_id'   => 'required',
        'image'         => 'image|mimes:jpeg,bmp,png'
    ];

    const ASSET_DIR = 'assets';

    #has one category
    public function category(){
        return $this->belongsTo('Modules\Category\Entities\Category');
    }

    public function records()
    {
        return $this->hasMany('Modules\Record\Entities\Record');
    }
}
