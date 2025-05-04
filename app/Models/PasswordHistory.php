<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password_history';

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
        'password_hash',
        'fecha_cambio',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the user that owns the password history.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Check if the password was used in the past.
     *
     * @param string $userId
     * @param string $password
     * @param int $limit Number of previous passwords to check
     * @return bool
     */
    public static function wasPasswordUsedBefore($userId, $password, $limit = 5)
    {
        $recentPasswords = self::where('usuario_id', $userId)
            ->orderBy('fecha_cambio', 'desc')
            ->limit($limit)
            ->get();

        foreach ($recentPasswords as $historyItem) {
            if (password_verify($password, $historyItem->password_hash)) {
                return true;
            }
        }

        return false;
    }
}
