<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Model;

class BigThink extends Model
{
    use HasOrganization;
    
    protected $guarded = [];


    public function post() {
        return $this->belongsTo(Post::class);
    }

}
