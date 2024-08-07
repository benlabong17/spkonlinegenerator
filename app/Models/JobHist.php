<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobHist extends Model
{

    protected $table = "job_hists";
    public $timestamps = false;
    protected $fillable = [
        'id',
        'job_category_id',
        'department_id',
        'wo_category',
        'job_category',
        'job_description',
        'active',
        'start_effective',
        'end_effective',
        'action',
        'created_by',
        'created_at',
    ];

    protected $dates = [
        'created_at',
    ];
}