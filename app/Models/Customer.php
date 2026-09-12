<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'address', 'phone', 'identity_card'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;
}
