<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    protected $table = 'purpose';
    protected $fillable = [
        'name',
    ];

    public function savingCategories()
    {
        return $this->hasMany(SavingCategory::class, 'purpose_id');
    }
}
