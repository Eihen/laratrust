<?php echo '<?php' ?>

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class LaratrustSetupTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create table for storing modules
        Schema::create('{{ $laratrust['tables']['modules'] }}', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create table for associating modules to permissions
        Schema::create('{{ $laratrust['tables']['permission_module'] }}', function(Blueprint $table) {
            $table->unsignedInteger('{{ $laratrust['foreign_keys']['module'] }}');
            $table->unsignedInteger('{{ $laratrust['foreign_keys']['permission'] }}');

            $table->foreign('{{ $laratrust['foreign_keys']['module'] }}')->references('id')->on('{{ $laratrust['tables']['modules'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('{{ $laratrust['foreign_keys']['permission'] }}')->references('id')->on('{{ $laratrust['tables']['permissions'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['{{ $laratrust['foreign_keys']['module'] }}', '{{ $laratrust['foreign_keys']['permission'] }}']);
        });

        // Create table for associating modules to roles
        Schema::create('{{ $laratrust['tables']['module_role'] }}', function(Blueprint $table) {
            $table->unsignedInteger('{{ $laratrust['foreign_keys']['module'] }}');
            $table->unsignedInteger('{{ $laratrust['foreign_keys']['role'] }}');

            $table->primary(['{{ $laratrust['foreign_keys']['module'] }}', '{{ $laratrust['foreign_keys']['role'] }}']);

            $table->foreign('{{ $laratrust['foreign_keys']['module'] }}')->references('id')->on('{{ $laratrust['tables']['modules'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('{{ $laratrust['foreign_keys']['role'] }}')->references('id')->on('{{ $laratrust['tables']['roles'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        // Create table for associating modules to users
        Schema::create('{{ $laratrust['tables']['module_user'] }}', function(Blueprint $table) {
            $table->unsignedInteger('{{ $laratrust['foreign_keys']['module'] }}');
            $table->unsignedInteger('{{ $laratrust['foreign_keys']['user'] }}');
            $table->string('user_type');
@if ($laratrust['use_teams'])
            $table->unsignedInteger('{{ $laratrust['foreign_keys']['team'] }}')->nullable();

            $table->primary(['{{ $laratrust['foreign_keys']['module'] }}', '{{ $laratrust['foreign_keys']['user'] }}', 'user_type', '{{ $laratrust['foreign_keys']['team'] }}']);

            $table->foreign('{{ $laratrust['foreign_keys']['team'] }}')->references('id')->on('{{ $laratrust['tables']['teams'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
@else

            $table->primary(['{{ $laratrust['foreign_keys']['module'] }}', '{{ $laratrust['foreign_keys']['user'] }}', 'user_type']);

@endif
            $table->foreign('{{ $laratrust['foreign_keys']['module'] }}')->references('id')->on('{{ $laratrust['tables']['modules'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
        });
@endif
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('{{ $laratrust['tables']['module_user'] }}');
        Schema::dropIfExists('{{ $laratrust['tables']['module_role'] }}');
        Schema::dropIfExists('{{ $laratrust['tables']['permission_module'] }}');
        Schema::dropIfExists('{{ $laratrust['tables']['modules'] }}');
    }
}
