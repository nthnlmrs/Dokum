<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Repository extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'github_repo_id', 'full_name', 'installation_id', 'is_active'];
    public function user() { return $this->belongsTo(User::class); }
    public function documentations() { return $this->hasMany(Documentation::class); }
    public function embeddings() { return $this->hasMany(Embedding::class); }
}