<?php
$files = glob("database/migrations/*_create_repositories_table.php");
if (empty($files)) {
    exec('php artisan make:model Repository -m');
    $files = glob("database/migrations/*_create_repositories_table.php");
}
$repoFile = $files[0];
$repoContent = <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repositories', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete();
            \$table->string('github_repo_id')->unique();
            \$table->string('full_name');
            \$table->string('installation_id')->nullable();
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};
EOT;
file_put_contents($repoFile, $repoContent);


$files = glob("database/migrations/*_create_prompt_templates_table.php");
if (empty($files)) {
    exec('php artisan make:model PromptTemplate -m');
    $files = glob("database/migrations/*_create_prompt_templates_table.php");
}
$templateFile = $files[0];
$templateContent = <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompt_templates', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete();
            \$table->string('name');
            \$table->text('system_prompt');
            \$table->boolean('is_default')->default(false);
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_templates');
    }
};
EOT;
file_put_contents($templateFile, $templateContent);


$files = glob("database/migrations/*_create_documentations_table.php");
if (empty($files)) {
    exec('php artisan make:model Documentation -m');
    $files = glob("database/migrations/*_create_documentations_table.php");
}
$docFile = $files[0];
$docContent = <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentations', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('repository_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('prompt_template_id')->nullable()->constrained()->nullOnDelete();
            \$table->string('title');
            \$table->longText('content')->nullable();
            \$table->string('pr_number')->nullable();
            \$table->string('status')->default('draft');
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentations');
    }
};
EOT;
file_put_contents($docFile, $docContent);


$files = glob("database/migrations/*_create_embeddings_table.php");
if (empty($files)) {
    exec('php artisan make:model Embedding -m');
    $files = glob("database/migrations/*_create_embeddings_table.php");
}
$embFile = $files[0];
$embContent = <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embeddings', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('repository_id')->constrained()->cascadeOnDelete();
            \$table->string('file_path');
            \$table->longText('content_chunk');
            \$table->vector('embedding', 1536)->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embeddings');
    }
};
EOT;
file_put_contents($embFile, $embContent);

$userMigration = glob("database/migrations/*_create_users_table.php")[0];
$userContent = file_get_contents($userMigration);
$userContent = str_replace(
    "\$table->string('email')->unique();",
    "\$table->string('email')->unique();\n            \$table->string('github_id')->nullable()->unique();\n            \$table->string('github_token')->nullable();\n            \$table->string('github_refresh_token')->nullable();",
    $userContent
);
file_put_contents($userMigration, $userContent);
