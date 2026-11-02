<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

#[Fillable(['id_number', 'name', 'gender', 'year', 'course', 'source_data'])]
class StudentReference extends Model
{
    protected function casts(): array
    {
        return [
            'source_data' => AsArrayObject::class,
        ];
    }
}
