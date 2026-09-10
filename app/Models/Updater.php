<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Updater extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public function organizations() {
        // Pivot si drží pôvodné meno z čias modelu Organization.
        return $this->belongsToMany(Canal::class, 'organization_updater');
    }

    public function posts() {
        return $this->belongsToMany(Post::class);
    }
}
