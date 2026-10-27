<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['clave', 'ultimo_valor'])]
class Secuencia extends Model
{
    protected $table = 'secuencias';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ultimo_valor' => 'integer',
        ];
    }
}
