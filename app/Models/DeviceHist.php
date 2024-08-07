<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceHist extends Model
{
    protected $table = "device_hists";
    public $timestamps = false;
    protected $fillable = [
        'id',
        'device_id',
        'device_name',
        'device_description',
        'brand',
        'location_id',
        'department_id',
        'device_category_id',
        'serial_number',
        'eq_id',
        'active',
        'start_effective',
        'end_effective',
        'action',
        'created_by',
        'created_at'
    ];

    protected $dates = [
        'created_at',
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
