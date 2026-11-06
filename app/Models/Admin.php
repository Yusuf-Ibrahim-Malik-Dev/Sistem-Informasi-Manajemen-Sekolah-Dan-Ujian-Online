<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['password'];

    public const SCOPE_SYSTEM = 'system';

    public const SCOPE_FOUNDATION = 'foundation';

    public const SCOPE_SCHOOL = 'school';

    protected $casts = [
        'ts' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level', 'level');
    }

    public function accesses()
    {
        return $this->hasMany(Access::class, 'level', 'level');
    }

    public function isAdmin(): bool
    {
        return $this->level === 'admin';
    }

    /**
     * Periksa akses menu spesifik pengguna terlebih dahulu.
     * Jika belum ada override personal, gunakan hak akses berdasarkan level/role.
     */
    public function hasAccess(string $kodeMenu): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $this->loadMissing(['personalMenuAccesses', 'accesses']);

        $personalAccess = $this->personalMenuAccesses
            ->firstWhere('kode_menu', $kodeMenu);

        if ($personalAccess !== null) {
            return $personalAccess->is_allowed;
        }

        return $this->accesses->contains('kode_menu', $kodeMenu);
    }

    /**
     * Periksa aksi spesifik pada sebuah menu: view, add, edit, delete, approve.
     * Override personal selalu menang atas hak akses level.
     */
    public function canMenuAction(string $kodeMenu, string $action = 'view'): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $action = strtolower($action);

        if (! in_array($action, ['view', 'add', 'create', 'edit', 'update', 'delete', 'approve', 'verify'], true)) {
            return false;
        }

        $this->loadMissing(['personalMenuAccesses', 'accesses']);

        $personalAccess = $this->personalMenuAccesses->firstWhere('kode_menu', $kodeMenu);

        if ($personalAccess !== null) {
            if (! $personalAccess->is_allowed) {
                return false;
            }

            return match ($action) {
                'view' => true,
                'add', 'create' => (bool) $personalAccess->can_add,
                'edit', 'update' => (bool) $personalAccess->can_edit,
                'delete' => (bool) $personalAccess->can_delete,
                'approve', 'verify' => (bool) ($personalAccess->can_approve ?? $personalAccess->can_edit),
                default => false,
            };
        }

        $access = $this->accesses->firstWhere('kode_menu', $kodeMenu);

        if ($access === null) {
            return false;
        }

        return match ($action) {
            'view' => true,
            'add', 'create' => (bool) $access->add,
            'edit', 'update' => (bool) $access->edit,
            'delete' => $access->can_delete === null ? (bool) $access->edit : (bool) $access->can_delete,
            'approve', 'verify' => $access->can_approve === null ? (bool) $access->edit : (bool) $access->can_approve,
            default => false,
        };
    }

    public function canAdd(string $kodeMenu): bool
    {
        return $this->canMenuAction($kodeMenu, 'add');
    }

    public function canEdit(string $kodeMenu): bool
    {
        return $this->canMenuAction($kodeMenu, 'edit');
    }

    public function canDelete(string $kodeMenu): bool
    {
        return $this->canMenuAction($kodeMenu, 'delete');
    }

    public function canApprove(string $kodeMenu): bool
    {
        return $this->canMenuAction($kodeMenu, 'approve');
    }

    /**
     * Ambil menu yang memang dapat dilihat oleh akun yang sedang login.
     */
    public function menus()
    {
        return Menu::query()
            ->orderBy('urutan')
            ->get()
            ->filter(fn (Menu $menu) => $this->hasAccess($menu->kode_menu))
            ->values();
    }

    public function preference()
    {
        return $this->hasOne(UserPreference::class, 'user_id');
    }

    public function personalMenuAccesses()
    {
        return $this->hasMany(UserMenuAccess::class, 'user_id');
    }

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'foundation_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function isSystemScope(): bool
    {
        return $this->scope_type === self::SCOPE_SYSTEM;
    }

    public function isFoundationScope(): bool
    {
        return $this->scope_type === self::SCOPE_FOUNDATION;
    }

    public function isSchoolScope(): bool
    {
        return $this->scope_type === self::SCOPE_SCHOOL;
    }

    public function accessibleSchools()
    {
        return match ($this->scope_type) {
            self::SCOPE_SCHOOL => School::whereKey($this->school_id),
            self::SCOPE_FOUNDATION => School::where('foundation_id', $this->foundation_id),
            default => School::query(),
        };
    }

    public function accessibleFoundations()
    {
        return match ($this->scope_type) {
            self::SCOPE_SCHOOL => Foundation::whereKey($this->foundation?->id),
            self::SCOPE_FOUNDATION => Foundation::whereKey($this->foundation_id),
            default => Foundation::query(),
        };
    }

    public function canAccessSchool(int $schoolId): bool
    {
        return $this->accessibleSchools()->whereKey($schoolId)->exists();
    }
}
