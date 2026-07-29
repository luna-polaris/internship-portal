<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $primaryKey = 'admin_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'admin_level',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function promote(): bool
    {
        return $this->update(['admin_level' => 'Super Admin']);
    }

    public function demote(): bool
    {
        return $this->update(['admin_level' => 'Moderator']);
    }
}
