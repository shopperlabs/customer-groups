<?php

declare(strict_types=1);

namespace Shopper\CustomerGroups\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Shopper\Core\Models\Traits\HasPublicId;
use Shopper\CustomerGroups\Database\Factories\CustomerGroupFactory;

/**
 * @property-read int $id
 * @property-read ?string $public_id
 * @property-read string $name
 * @property-read string $slug
 * @property-read ?string $description
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
class CustomerGroup extends Model
{
    /** @use HasFactory<CustomerGroupFactory> */
    use HasFactory;

    use HasPublicId;

    protected $guarded = [];

    public function getTable(): string
    {
        return shopper_table('customer_groups');
    }

    /**
     * @return BelongsToMany<Model, $this>
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(
            config('auth.providers.users.model'),
            shopper_table('customer_group_user'),
            'customer_group_id',
            'user_id',
        )->withTimestamps();
    }

    /**
     * @param  Builder<CustomerGroup>  $query
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory(): CustomerGroupFactory
    {
        return CustomerGroupFactory::new();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
