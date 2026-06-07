<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['type', 'access_level', 'login', 'password', 'nom', 'prenom', 'email', 'email_secondaire', 'telephone', 'suspended_at'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /** Login du super-admin principal : protégé contre suspension / rétrogradation. */
    public const SUPER_ADMIN_LOGIN = 'antoinepw';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'suspended_at' => 'datetime',
        ];
    }

    /**
     * Nom complet : "Prénom Nom".
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->prenom} {$this->nom}"),
        );
    }

    /**
     * Note pense-bête de l'utilisateur (une seule).
     */
    public function note(): HasOne
    {
        return $this->hasOne(Note::class);
    }

    /**
     * Pages mises en favori par l'utilisateur.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Accès accordés (admin restreint) vers des ressources précises.
     */
    public function accessGrants(): HasMany
    {
        return $this->hasMany(AccessGrant::class);
    }

    /**
     * Raccourci : l'utilisateur est-il administrateur ?
     */
    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    /**
     * Admin avec accès total (voit tout, ignore les grants) ?
     */
    public function hasFullAccess(): bool
    {
        return $this->access_level === 'full';
    }

    /**
     * Compte suspendu (désactivé sans suppression) ?
     */
    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    /**
     * Super-admin principal, protégé contre suspension / rétrogradation.
     */
    public function isSuperAdmin(): bool
    {
        return $this->login === self::SUPER_ADMIN_LOGIN;
    }

    /**
     * L'admin a-t-il accès à cette ressource précise ?
     */
    public function canAccess(Model $resource): bool
    {
        if ($this->hasFullAccess()) {
            return true;
        }

        return $this->accessGrants()
            ->where('grantable_type', $resource->getMorphClass())
            ->where('grantable_id', $resource->getKey())
            ->exists();
    }
}
