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
        // Create table for storing teams
        Schema::create('{{ $laratrust['tables']['teams'] }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('{{ $laratrust['tables']['role_user'] }}', function (Blueprint $table) {
            // Drop role foreign key and primary key
            $table->dropForeign(['{{ $laratrust['foreign_keys']['role'] }}']);
            $table->dropPrimary(['{{ $laratrust['foreign_keys']['user'] }}', '{{ $laratrust['foreign_keys']['role'] }}', 'user_type']);

            // Add {{ $laratrust['foreign_keys']['team'] }} column
            $table->unsignedInteger('{{ $laratrust['foreign_keys']['team'] }}')->nullable();

            // Create foreign keys
            $table->foreign('{{ $laratrust['foreign_keys']['role'] }}')->references('id')->on('{{ $laratrust['tables']['roles'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('{{ $laratrust['foreign_keys']['team'] }}')->references('id')->on('{{ $laratrust['tables']['teams'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            // Create a unique key
            $table->unique(['{{ $laratrust['foreign_keys']['user'] }}', '{{ $laratrust['foreign_keys']['role'] }}', 'user_type', '{{ $laratrust['foreign_keys']['team'] }}']);
        });

        Schema::table('{{ $laratrust['tables']['permission_user'] }}', function (Blueprint $table) {
           // Drop permission foreign key and primary key
            $table->dropForeign(['{{ $laratrust['foreign_keys']['permission'] }}']);
            $table->dropPrimary(['{{ $laratrust['foreign_keys']['permission'] }}', '{{ $laratrust['foreign_keys']['user'] }}', 'user_type']);

            $table->foreign('{{ $laratrust['foreign_keys']['permission'] }}')->references('id')->on('{{ $laratrust['tables']['permissions'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            // Add {{ $laratrust['foreign_keys']['team'] }} column
            $table->unsignedInteger('{{ $laratrust['foreign_keys']['team'] }}')->nullable();

            $table->foreign('{{ $laratrust['foreign_keys']['team'] }}')->references('id')->on('{{ $laratrust['tables']['teams'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['{{ $laratrust['foreign_keys']['user'] }}', '{{ $laratrust['foreign_keys']['permission'] }}', 'user_type', '{{ $laratrust['foreign_keys']['team'] }}']);
        });

@if ($laratrust['use_modules'])
        //FixMe temp solution for enabling teams on already enabled modules assuring nothing will break
        if (Schema::hasTable('{{ $laratrust['tables']['module_user'] }}'))
        {
            Schema::table('{{ $laratrust['tables']['module_user'] }}', function (Blueprint $table) {
                $table->dropPrimary['{{ $laratrust['foreign_keys']['module'] }}', '{{ $laratrust['foreign_keys']['user'] }}', 'user_type']);

                $table->unsignedInteger('{{ $laratrust['foreign_keys']['team'] }}')->nullable();

                $table->primary['{{ $laratrust['foreign_keys']['module'] }}', '{{ $laratrust['foreign_keys']['user'] }}', 'user_type', '{{ $laratrust['foreign_keys']['team'] }}']);

                $table->foreign('{{ $laratrust['foreign_keys']['team'] }}')->references('id')->on('{{ $laratrust['tables']['teams'] }}')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }
@endif
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
