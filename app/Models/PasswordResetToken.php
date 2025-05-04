<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PasswordResetToken extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password_reset_tokens';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true; // Cambiar a true

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int'; // Cambiar a int

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'usuario_id',
        'token',
        'fecha_creacion',
        'fecha_expiracion',
        'utilizado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'utilizado' => 'boolean',
    ];

    /**
     * Get the user that owns the reset token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Check if the token is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return !$this->utilizado && now()->lt($this->fecha_expiracion);
    }

    /**
     * Mark token as used.
     *
     * @return void
     */
    public function markAsUsed()
    {
        $this->utilizado = true;
        $this->save();
    }

    /**
     * Create a new reset token for a user.
     *
     * @param string $userId
     * @param int $expirationHours
     * @return self
     */
    public static function createForUser($userId, $expirationHours = 24)
    {
        // Invalidate any existing tokens
        self::where('usuario_id', $userId)
            ->where('utilizado', false)
            ->update(['utilizado' => true]);

        // Create new token
        return self::create([
            'usuario_id' => $userId,
            'token' => bin2hex(random_bytes(50)), // Generate a secure random token
            'fecha_creacion' => now(),
            'fecha_expiracion' => now()->addHours($expirationHours),
            'utilizado' => false,
        ]);
    }
}
