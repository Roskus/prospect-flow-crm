<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Email\Attach;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OAT;

#[OAT\Schema(schema: 'Email', required: ['subject', 'body'])]
class Email extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'email';

    const DRAFT = 'draft';

    const QUEUE = 'queue';

    const SENT = 'sent';

    const ERROR = 'error';

    #[OAT\Property(type: 'int', example: 1)]
    private ?int $id;

    #[OAT\Property(description:'Subject of the email', type: 'string', example: 'Email subject')]
    protected string $subject;

    #[OAT\Property(description:'Body of the email', type: 'string', example: 'Email body')]
    protected string $body;

    public function attachments(): HasMany
    {
        return $this->hasMany(Attach::class);
    }

    public function getAll()
    {
        return Email::orderBy('created_at', 'DESC')->get();
    }

    public function getAllByCompanyId(int $company_id, ?string $search = null, ?array $filters = null)
    {
        if (empty($search)) {
            $email = Email::where('company_id', $company_id);
        } else {
            $email = Email::where('to', 'LIKE', "%$search%")
                            ->orWhere('subject', 'LIKE', "%$search%");
        }

        if (is_array($filters)) {
            foreach ($filters as $key => $filter) {
                $email->where($key, $filter);
            }
        }

        return $email->paginate(10);
    }
}
