<?php echo '<?php' ?>

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CerberusSetupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        // Create table for storing sites
        // Feel free to add/remove fields before running the migration.
        if (!Schema::hasTable('{{ $sitesTable }}') {
            Schema::create('{{ $sitesTable }}', function (Blueprint $table) {
                $table->increments('id');
                $table->string('domain')->unique();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }

        // Create table for storing roles
        Schema::create('{{ $rolesTable }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create table for associating roles to users (Many-to-Many)
        Schema::create('{{ $roleUserTable }}', function (Blueprint $table) {
            $table->integer('{{ $userFK }}')->unsigned();
            $table->integer('{{ $roleFK }}')->unsigned();
            $table->integer('{{ $siteFK }}')->unsigned();

            $table->foreign('{{ $userFK }}')->references('{{ $userKeyName }}')->on('{{ $usersTable }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('{{ $roleFK }}')->references('id')->on('{{ $rolesTable }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('{{ $siteFK }}')->references('id')->on('{{ $sitesTable }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['user_id', 'role_id', 'site_id']);
        });

        // Create table for storing permissions
        Schema::create('{{ $permissionsTable }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create table for associating permissions to roles (Many-to-Many)
        Schema::create('{{ $permissionRoleTable }}', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->timestamps();

            $table->foreign('permission_id')->references('id')->on('{{ $permissionsTable }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('{{ $rolesTable }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('{{ $permissionRoleTable }}');
        Schema::drop('{{ $permissionsTable }}');
        Schema::drop('{{ $roleUserTable }}');
        Schema::drop('{{ $rolesTable }}');
    }
}
