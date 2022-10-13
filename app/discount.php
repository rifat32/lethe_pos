<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class discount extends Model
{
    use SoftDeletes;
     /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $table = 'discount';
    protected $dates = ['deleted_at'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
