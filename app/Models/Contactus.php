<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contactus extends Model
{
    use HasFactory;

    protected $table = 'contactuses';

    public $fillable = [
        'name', 'email', 'number', 'address', 'detail',
        // Agent D — Contact/Messages upgrade columns (all nullable, added guarded)
        'category', 'status', 'reply_body', 'replied_by', 'replied_at', 'classified_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'classified_at' => 'datetime',
    ];

    public function replier()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /** Config-driven label for this row's category. */
    public function categoryLabel(): string
    {
        return config('admin_contacts.categories.' . $this->category . '.label', ucfirst((string) $this->category));
    }

    /** Config-driven badge color (AdminLTE/Bootstrap class) for this row's category. */
    public function categoryColor(): string
    {
        return config('admin_contacts.categories.' . $this->category . '.color', 'secondary');
    }

    /** Config-driven label for this row's status. */
    public function statusLabel(): string
    {
        return config('admin_contacts.statuses.' . $this->status . '.label', ucfirst((string) $this->status));
    }

    /** Config-driven badge color (AdminLTE/Bootstrap class) for this row's status. */
    public function statusColor(): string
    {
        return config('admin_contacts.statuses.' . $this->status . '.color', 'secondary');
    }

    /** Badge HTML for status (server-rendered views). */
    public function statusBadge(): string
    {
        return '<span class="dp-badge badge badge-' . e($this->statusColor()) . '">' . e($this->statusLabel()) . '</span>';
    }
}
