<?php
$userModel = <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected \$fillable = ['name', 'email', 'password', 'github_id', 'github_token', 'github_refresh_token'];
    protected \$hidden = ['password', 'remember_token', 'github_token', 'github_refresh_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed']; }
    public function repositories() { return \$this->hasMany(Repository::class); }
    public function promptTemplates() { return \$this->hasMany(PromptTemplate::class); }
}
EOT;
file_put_contents('app/Models/User.php', $userModel);

$repoModel = <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Repository extends Model
{
    use HasFactory;
    protected \$fillable = ['user_id', 'github_repo_id', 'full_name', 'installation_id', 'is_active'];
    public function user() { return \$this->belongsTo(User::class); }
    public function documentations() { return \$this->hasMany(Documentation::class); }
    public function embeddings() { return \$this->hasMany(Embedding::class); }
}
EOT;
file_put_contents('app/Models/Repository.php', $repoModel);

$promptModel = <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PromptTemplate extends Model
{
    use HasFactory;
    protected \$fillable = ['user_id', 'name', 'system_prompt', 'is_default'];
    public function user() { return \$this->belongsTo(User::class); }
    public function documentations() { return \$this->hasMany(Documentation::class); }
}
EOT;
file_put_contents('app/Models/PromptTemplate.php', $promptModel);

$docModel = <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Documentation extends Model
{
    use HasFactory;
    protected \$fillable = ['repository_id', 'prompt_template_id', 'title', 'content', 'pr_number', 'status'];
    public function repository() { return \$this->belongsTo(Repository::class); }
    public function promptTemplate() { return \$this->belongsTo(PromptTemplate::class); }
}
EOT;
file_put_contents('app/Models/Documentation.php', $docModel);

$embModel = <<<EOT
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\Vector;
use Pgvector\Laravel\HasVectors;
class Embedding extends Model
{
    use HasFactory, HasVectors;
    protected \$fillable = ['repository_id', 'file_path', 'content_chunk', 'embedding'];
    protected \$casts = ['embedding' => Vector::class];
    public function repository() { return \$this->belongsTo(Repository::class); }
}
EOT;
file_put_contents('app/Models/Embedding.php', $embModel);
