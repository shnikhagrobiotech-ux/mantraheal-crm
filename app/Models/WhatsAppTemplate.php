<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'template_body',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function render(array $data): string
    {
        $rendered = $this->template_body;
        foreach ($data as $key => $value) {
            $rendered = str_replace('{{' . $key . '}}', $value, $rendered);
        }
        return $rendered;
    }
}
