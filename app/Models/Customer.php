<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\AssignedSellerScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations\OpenApi as OA;
use Squire\Models\Country;
use Yajra\Auditable\AuditableWithDeletesTrait;

/**
 *  @OA\Schema(
 *    schema="Customer",
 *    type="object",
 *    required={"name", "country"},
 *    @OA\Property(
 *        property="name",
 *        description="Name of the customer",
 *        type="string",
 *        example="My Company"
 *    ),
 *    @OA\Property(
 *        property="business_name",
 *        description="Business name or legal name of the company",
 *        type="string",
 *        example="My Company S.A."
 *    ),
 *    @OA\Property(
 *        property="dob",
 *        description="Date of fundation of the customer",
 *        type="date",
 *        example="1990-02-20"
 *    ),
 *    @OA\Property(
 *        property="vat",
 *        description="VAT/NIF of the customer",
 *        type="string",
 *        example="ESX1234567X"
 *    ),
 *    @OA\Property(
 *        property="phone",
 *        description="Phone of the customer",
 *        type="string",
 *        example="+3464500000"
 *    ),
 *    @OA\Property(
 *        property="phone2",
 *        description="Phone2 of the customer",
 *        type="string",
 *        example="+3464500000"
 *    ),
 *    @OA\Property(
 *        property="mobile",
 *        description="Mobile of the customer",
 *        type="string",
 *        example="+3464500000"
 *    ),
 *    @OA\Property(
 *        property="email",
 *        description="Email of the customer",
 *        type="string",
 *        format="email",
 *        example="jhon.doe@email.com"
 *    ),
 *    @OA\Property(
 *        property="email2",
 *        description="Email2 of the customer",
 *        type="string",
 *        format="email",
 *        example="jhon.doe@email.com"
 *    )
 * )
 */
class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AuditableWithDeletesTrait;

    const OPEN = 'open'; //New

    const IN_PROGRESS = 'in_progress';

    const CONVERTED = 'converted'; // Promoted to customer

    const CLOSED = 'closed';

    protected $table = 'customer';

    protected $fillable = [
        'external_id',
        'company_id',
        'name',
        'business_name',
        'dob',
        'vat',
        'phone',
        'phone2',
        'mobile',
        'email',
        'email2',
        'website',
        'linkedin',
        'facebook',
        'instagram',
        'twitter',
        'youtube',
        'tiktok',
        'notes',
        'seller_id',
        'country_id',
        'province',
        'city',
        'locality',
        'street',
        'zipcode',
        'schedule_contact',
        'industry_id',
        'latitude',
        'longitude',
        'opt_in',
        'tags',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'schedule_contact' => 'datetime',
        'tags' => 'array',
    ];

    protected $hidden = [
        'company_id',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $with = ['country', 'seller', 'industry'];

    public function company(): HasOne
    {
        return $this->hasOne(\App\Models\Company::class, 'id', 'company_id');
    }

    public function seller(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'seller_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function industry(): HasOne
    {
        return $this->hasOne(Industry::class, 'id', 'industry_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'customer_id', 'id');
    }

    public function getAll(): Collection
    {
        return Customer::all();
    }

    public function getAllByCompanyId(int $company_id, ?string $search = null, ?array $filters = null, int $limit = 50): mixed
    {
        $customers = Customer::where('company_id', $company_id);
        if (! empty($search)) {
            $customers->where('name', 'LIKE', "%$search%")
                      ->orWhere('business_name', 'LIKE', "%$search%")
                      ->orWhere('external_id', '=', "$search")
                      ->orWhere('phone', 'LIKE', "%$search%")
                      ->orWhere('tags', 'LIKE', "%$search%");
        }

        if (is_array($filters)) {
            foreach ($filters as $key => $filter) {
                $customers->where($key, $filter);
            }
        }

        return $customers->paginate($limit);
    }

    public function getCountByCompany(int $company_id): int
    {
        return Customer::where('company_id', $company_id)->count();
    }

    public function getLatestByCompany(int $company_id, int $limit = 10)
    {
        $customers = Customer::where('company_id', $company_id);
        $customers->orderBy('created_at', 'DESC');

        return $customers->limit($limit)->get();
    }

    public static function getStatus(): array
    {
        return [
            self::OPEN => 'Open',
            self::IN_PROGRESS => 'In progress',
            self::CONVERTED => 'Converted',
            self::CLOSED => 'Closed',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new AssignedSellerScope);
    }
}
