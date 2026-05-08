<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\Vector;
use Pgvector\Laravel\HasVectors;
class Embedding extends Model
{
    use HasFactory, HasVectors;
    protected $fillable = ['repository_id', 'file_path', 'content_chunk', 'embedding'];
    protected $casts = ['embedding' => Vector::class];
    public function repository() { return $this->belongsTo(Repository::class); }
}