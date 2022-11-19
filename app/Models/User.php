<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\QueryFilters\Active;
use App\QueryFilters\Sort;
use AppHelper;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use CascadeSoftDeletes;

    protected array $cascadeDeletes = ['userProfile'];
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'username',
        'password',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (User $user) {
            $user->email = AppHelper::appendTimestamp($user->email, '::deleted_');
            $user->username = AppHelper::appendTimestamp($user->username, '::deleted_');
            $user->saveQuietly();
        });
    }

    /**
     * A User has exactly one profile information
     *
     * @return HasOne
     */
    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Hash the password whenever it is changed
     *
     * @return Attribute
     */
    public function password(): Attribute
    {
        return Attribute::set(
            fn ($value) => Hash::make($value)
        );
    }

    /**
     * Get all users with included query filters
     *
     * @param array ...$eagerLoadRelations
     * @return Collection
     */
    public static function getAllUsersFromPipeline(array ...$eagerLoadRelations): Collection
    {
        $query = User::query();

        if (!empty($eagerLoadRelations)) {
            $query->with(...$eagerLoadRelations);
        }

        /** @var \Illuminate\Support\Collection $users */
        return app(Pipeline::class)
            ->send($query)
            ->through([
                Sort::class,
                Active::class
            ])
            ->thenReturn()
            ->get();
    }
}
