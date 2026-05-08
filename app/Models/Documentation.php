<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Documentation extends Model
{
    use HasFactory;
    protected $fillable = ['repository_id', 'prompt_template_id', 'title', 'content', 'pr_number', 'status'];
    public function repository() { return $this->belongsTo(Repository::class); }
    public function promptTemplate() { return $this->belongsTo(PromptTemplate::class); }
}