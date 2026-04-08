<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

#[Fillable(['email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;
    protected $connection = 'mongodb';
    protected $collection = 'users';
    /**
     * Các trường được phép thêm dữ liệu
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];
    /**
     * CỰC KỲ QUAN TRỌNG: Ẩn các trường này khi trả về API cho React
     * (Nếu không có mảng này, API sẽ trả về cả mật khẩu đã mã hóa)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function createToken(string $name, array $abilities = ['*'], \DateTimeInterface $expiresAt = null)
    {
        $plainTextToken = Str::random(40);

        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        // Trả về một class ẩn danh đóng gói Token y hệt như Laravel
        // Nhưng không bị ép buộc kiểu dữ liệu SQL
        return new class($token, $token->getKey().'|'.$plainTextToken) {
            public $accessToken;
            public $plainTextToken;

            public function __construct($accessToken, $plainTextToken)
            {
                $this->accessToken = $accessToken;
                $this->plainTextToken = $plainTextToken;
            }
        };
    }
}
