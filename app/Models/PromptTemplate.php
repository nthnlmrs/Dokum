<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PromptTemplate extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'name', 'system_prompt', 'is_default'];
    public function user() { return $this->belongsTo(User::class); }
    public function documentations() { return $this->hasMany(Documentation::class); }
}