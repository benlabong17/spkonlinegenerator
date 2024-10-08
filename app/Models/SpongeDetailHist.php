<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpongeDetailHist extends Model
{
    protected $table = "sponge_detail_history";
    protected $fillable = [
        'id',
        'sponge_detail_id',
        'wo_number_id',
        'cr_number',
        'wp_number',
        'location_id',
        'device_id',
        'disturbance_category',
        'wo_description',
        'job_description',
        'job_executor',
        'job_supervisor',
        'job_aid',
        'executor_progress',
        'executor_desc',
        'wo_attachment1',
        'wo_attachment2',
        'wo_attachment3',
        'job_attachment1',
        'job_attachment2',
        'job_attachment3',
        'start_at',
        'estimated_end',
        'close_at',
        'canceled_at',
        'action',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('App\User', 'updated_by');
    }
}
