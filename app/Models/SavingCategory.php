<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingCategory extends Model
{
    protected $table = 'saving_categories';
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'goal_amount',
        'current_amount',
        'duration',
        'unit',
        'frequency',
        'icon',
        'purpose',
    ];

    protected $casts = [
        'goal_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savings()
    {
        return $this->hasMany(Saving::class, 'saving_category_id');
    }

    public function purpose()
    {
        return $this->belongsTo(Purpose::class, 'purpose_id');
    }
}
