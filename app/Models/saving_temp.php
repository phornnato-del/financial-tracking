<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
   protected $table = 'saving';
    protected $fillable = [
         'amount',
         'saving_category_id',
         'user_id',
         'description',
    ];

    public function savingCategory()
    {
        return $this->belongsTo(SavingCategory::class, 'saving_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
