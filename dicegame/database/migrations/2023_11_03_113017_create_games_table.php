<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    /**
     * Run the migrations.
     */
    
    public function up(): void {

        Schema::create('games', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key to the users table
            $table->integer('dice_A');
            $table->integer('dice_B');
            $table->integer('result');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });

            // Create a trigger to calculate the result
            DB::unprepared('

                CREATE TRIGGER calculate_result
                BEFORE INSERT ON games
                FOR EACH ROW
                BEGIN
                    SET NEW.result = NEW.dice_A + NEW.dice_B;
                END
            ');

    }

    /**
     * Reverse the migrations.
     */

    public function down(): void {

        DB::unprepared('DROP TRIGGER IF EXISTS calculate_result');
        Schema::dropIfExists('games');

    }

};