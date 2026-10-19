<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class System extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'slug',
        'url',
        'ping_url',
        'description',
        'badge',
        'tag',
        'features',
        'logo',
        'brand_color',
        'brand_hover_color',
        'bg_gradient',
        'is_active',
        'is_online',
        'last_ping_at',
        'default_sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'last_ping_at' => 'datetime',
        'default_sort_order' => 'integer',
    ];

    /**
     * Get the full public URL for the system logo.
     */
    public function getLogoUrlAttribute(): string
    {
        if (! $this->logo) {
            return asset('images/LODISv2.png');
        }

        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }

        if (str_starts_with($this->logo, 'images/')) {
            return asset($this->logo);
        }

        return asset('images/systems/' . $this->logo);
    }

    /**
     * Transform database record directly into the Vue slideshow prop array.
     */
    public function toSlideshowArray(): array
    {
        $gradient = $this->bg_gradient ?? '';
        if ($gradient && ! str_contains($gradient, 'gradient')) {
            $gradient = "linear-gradient(135deg, {$gradient})";
        }

        return [
            'name'        => $this->name,
            'badge'       => $this->badge ?? 'Integrated Sub-System',
            'logo'        => $this->logo_url,
            'description' => $this->description ?? '',
            'brand_color' => $this->brand_color ?? '#0f172a',
            'gradient'    => $gradient ?: 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
            'tag'         => $this->tag ?? strtoupper($this->slug ?? $this->name),
            'features'    => $this->features ?? [],
        ];
    }

    /**
     * Resolve system branding from DB or fall back to LODIS default (#00687A).
     */
    public static function resolveForSso(?string $redirectUrl = null, ?string $slug = null): self
    {
        if ($redirectUrl) {
            $host = parse_url($redirectUrl, PHP_URL_HOST);
            if ($host) {
                $matched = static::where('is_active', true)
                    ->where('url', 'like', "%{$host}%")
                    ->first();

                if ($matched) {
                    return $matched;
                }
            }
        }

        if ($slug && $slug !== 'lodis') {
            $matched = static::where('is_active', true)
                ->where('slug', $slug)
                ->first();

            if ($matched) {
                return $matched;
            }
        }

        return static::where('is_active', true)->where('slug', 'lodis')->first()
            ?? static::lodisDefault();
    }

    /**
     * Default LODISv2 branding instance.
     */
    public static function lodisDefault(): self
    {
        $instance = new static();
        $instance->name = 'LODISv2';
        $instance->slug = 'lodis';
        $instance->logo = 'images/LODISv2.png';
        $instance->badge = 'Central Web Application Hub';
        $instance->tag = 'Core Portal';
        $instance->brand_color = '#00687A';
        $instance->brand_hover_color = '#005261';
        $instance->bg_gradient = 'linear-gradient(135deg, #020617 0%, #0f172a 50%, #003843 100%)';
        $instance->description = 'Secure single-sign-on launcher providing centralized access to internal operational platforms, data pipelines, and administrative management applications.';
        $instance->features = [
            ['title' => 'Single Sign-On', 'description' => 'Authenticate once to access all authorized sub-systems.'],
            ['title' => 'Zero-Trust Guard', 'description' => 'Workstation multi-factor authorization enforced across gateways.']
        ];

        return $instance;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'system_user')
            ->withPivot(['role', 'has_access', 'custom_order'])
            ->withTimestamps();
    }
}