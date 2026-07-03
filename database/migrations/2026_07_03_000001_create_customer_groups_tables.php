<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shopper\Core\Helpers\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(shopper_table('customer_groups'), function (Blueprint $table): void {
            $this->addCommonFields($table);
            $table->ulid('public_id')->nullable()->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->index('is_active');
        });

        Schema::create(shopper_table('customer_group_user'), function (Blueprint $table): void {
            $this->addForeignKey($table, 'customer_group_id', $this->getTableName('customer_groups'), false);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->primary(['customer_group_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(shopper_table('customer_group_user'));
        Schema::dropIfExists(shopper_table('customer_groups'));
    }
};
