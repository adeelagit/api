<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class company extends Model
{
    protected $table = 'restaurants';

    protected $fillable = [
        'name',
        'slug',
        'uuid',
        'address',
        'location',
        'mobile_number',
        'subdomain'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Set the UUID and Slug automatically on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            $company->uuid = (string) Str::uuid();
            $company->slug = Str::slug($company->name);
        });
    }
}
