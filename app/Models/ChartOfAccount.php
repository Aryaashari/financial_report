<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $primaryKey = 'code';

    protected $fillable = ['code', 'name', 'category_name', 'user_id'];

    public function category() {
        return $this->belongsTo(Category::class, 'category_name');
    }

    public function transactions() {
        return $this->hasMany(Transaction::class, 'coa_code', 'code');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
