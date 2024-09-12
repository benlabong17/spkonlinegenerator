@extends('layouts.layout')

@section('auth')
<h4 class="pull-left page-title">Tambah Data Peralatan</h4>
<ol class="breadcrumb pull-right">
    <li><a href="#">{{Auth::user()->name}}</a></li>
    <li class="active">Tambah Data Peralatan</li>
</ol>
<div class="clearfix"></div>
@endsection

@section('content')
<div class="container">
    <div class="card-header">
        <div class="btn-group" role="group">
            <div class="form-group">
                <button type="button" name="back" id="back" class="btn btn-secondary" onclick="doBack();"><i class="fa fa-arrow-left"></i> {{ucwords(__('Kembali'))}}</button>
                <button type="button" name="save" id="saveBtn" class="btn btn-primary" onclick="showModal();"><i class="fa fa-fw fa-save"></i> {{ucwords(__('Simpan'))}}</button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <form method="POST" id="search-form" class="form" role="form">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Form Data Peralatan</h3>
                    </div>
                    <div class="panel-body">
                        <span id="form_result"></span>
                        {{-- NAMA PERALATAN --}}
                        <div class="row mb-2">
                            <label class="col-md-2">NAMA PERALATAN *</label>
                            <div class="col-md-6">
                                <input maxlength="150" id="device_name" type="text" class="text-uppercase form-control" name="device_name" title="NAMA PERALATAN" placeholder="NAMA PERALATAN">
                                <input name="device_name_id" id="location_name_id" type="hidden"/>
                            </div>
                        </div>
                        <br>

                        {{-- DESKRIPSI --}}
                        <div class="row mb-2">
                            <label class="col-md-2">DESKRIPSI</label>
                            <div class="col-md-6">
                                <textarea maxlength="100" required class="form-control" rows="5" id="description" type="text" class="text-uppercase form-control" name="description" title="DESKRIPSI" placeholder="DESKRIPSI"></textarea>
                            </div>
                        </div>
                        <br>

                        {{-- BRAND --}}
                        <div class="row mb-2">
                            <label class="col-md-2">BRAND *</label>
                            <div class="col-md-6">
                                <input maxlength="50" id="brand" type="text" class="text-uppercase form-control" name="brand" title="BRAND" placeholder="BRAND">
                                <input name="brand_id" id="location_name_id" type="hidden"/>
                            </div>
                        </div>
                        <br>

                        {{-- LOKASI --}}
                        <div class="row mb-2">
                            <label class="col-md-2">LOKASI *</label>
                            <div class="col-md-6">
                                <select title="LOKASI" id="location" class="form-control">
                                    <option value="" selected>PILIH SALAH SATU</option>
                                    @foreach ($locations as $item)
                                        <option value={{ $item->id }}>{{ $item->location }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <br>

                        {{-- DEPARTEMEN --}}
                        <div class="row mb-2">
                            <label class="col-md-2">DEPARTEMEN *</label>
                            <div class="col-md-6">
                                <select title="DEPARTEMEN" id="department" class="form-control">
                                    <option value="" selected>PILIH SALAH SATU</option>
                                    @foreach ($departments as $item)
                                        <option value={{ $item->id }}>{{ $item->department }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <br>

                        {{-- KATEGORI ALAT --}}
                        <div class="row mb-2">
                            <label class="col-md-2">KATEGORI ALAT *</label>
                            <div class="col-md-6">
                                <select title="KATEGORI ALAT" id="device_category" class="form-control">
                                    <option value="" selected>PILIH SALAH SATU</option>
                                    @foreach ($deviceCategories as $item)
                                        <option value={{ $item->id }}>{{ $item->device_category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <br>

                        {{-- NOMOR SERI --}}
                        <div class="row mb-2">
                            <label class="col-md-2">NOMOR SERI *</label>
                            <div class="col-md-6">
                                <input maxlength="100" id="serial_number" type="text" class="text-uppercase form-control" name="serial_number" title="NOMOR SERI" placeholder="NOMOR SERI">
                                <input name="serial_number_id" id="location_name_id" type="hidden"/>
                            </div>
                        </div>
                        <br>

                        {{-- EQ ID --}}
                        <div class="row mb-2">
                            <label class="col-md-2">NOMOR AKTIVA *</label>
                            <div class="col-md-6">
                                <input maxlength="50" id="activa_number" type="text" class="text-uppercase form-control" name="activa_number" title="NOMOR AKTIVA" placeholder="NOMOR AKTIVA">
                                <input name="activa_number_id" id="location_name_id" type="hidden"/>
                            </div>
                        </div>
                        <br>
                        <br>
                        <br>
                    </div> <!-- panel-body -->
                </div> <!-- panel -->
            </form>
        </div> <!-- col -->
    </div>
</div>

<body>
    <div id="modal" class="modal-container"> 
        <div class="modal-content"> 
  
            <h2>Konfirmasi</h2> 
            <p class="confirmation-message"> 
                Anda yakin akan menyimpan? 
            </p> 
  
            <div class="button-container"> 
                <button id="cancelBtn" class="btn btn-secondary"> Batal </button> 
                <button id="actionBtn" class="btn btn-primary"> Ya </button> 
            </div> 
        </div> 
    </div> 
</body>

<style>
    .signature-canvas {
        border: 2px solid #000;
        margin-bottom: 10px;
    }
</style>

<script>
    function doSave(){
        hideModal();
        $('#form_result').html('');

        var device_name      = $('#device_name').val();
        var description      = $('#description').val();
        var brand            = $('#brand').val();
        var location         = $('#location').val();
        var department       = $('#department').val();
        var device_category  = $('#device_category').val();
        var serial_number    = $('#serial_number').val();
        var activa_number    = $('#activa_number').val();
        
        artLoadingDialogDo("Please wait, we process your request..",function(){
            $.ajax({
                url : '{!! route('masters/device/create-new/create') !!}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{!!csrf_token()!!}'
                },
                dataType:"json",
                data: {
                    'device_name'     : device_name,
                    'description'     : description,
                    'brand'           : brand,
                    'location'        : location,
                    'department'      : department,
                    'device_category' : device_category,
                    'serial_number'   : serial_number,
                    'activa_number'   : activa_number,
                },
                success: function(data){
                    artLoadingDialogClose();
                    if(data.errors)
                    {
                        $('#form_result').html(data.message);
                    }
                    if(data.success) 
                    {
                        $('#form_result').html(data.message);
                        $('#device_name').val('');
                        $('#description').val('');
                        $('#brand').val('');
                        $('#location').val('');
                        $('#department').val('');
                        $('#device_category').val('');
                        $('#serial_number').val('');
                        $('#activa_number').val('');
                        setTimeout(function(){ window.location.href = '{{url('masters/device/index')}}'; }, 1500);
                    }  
                },
                error: function(data) {
                    artLoadingDialogClose();
                    html = '<div class="alert alert-danger">Terjadi kesalahan</div>';
                    $('#form_result').html(html);
                    if(data.responseJSON.message) {
                        var target = data.responseJSON.errors;
                        for (var k in target){
                            if(!Array.isArray(target[k]['0']))
                            {
                                var msg = target[k]['0'];
                                artCreateFlashMsg(msg,"danger",true);
                            }
                        }
                    }
                }
            });
        });
        return false;
    }

    function doBack(){
        setTimeout(function(){ window.location.href = '{{url('masters/device/index')}}'; }, 100);
    }

    function showModal() { 
        modal.style.display = 'flex'; 
        $(effective_date).prop("disabled", true);
        $(effective_date).blur(); 
    } 

    // Hide modal function 
    function hideModal() { 
        modal.style.display = 'none'; 
        $(effective_date).prop("disabled", false);
    } 

    cancelBtn.addEventListener('click', hideModal); 
    actionBtn.addEventListener('click', doSave);
</script>

@endsection