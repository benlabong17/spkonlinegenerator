@extends('layouts.layout')

@section('auth')
<h4 class="pull-left page-title">Tambah Otorisasi</h4>
<ol class="breadcrumb pull-right">
    <li><a href="#">{{Auth::user()->name}}</a></li>
    <li class="active">Tambah Otorisasi</li>
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
                        <h3 class="panel-title">Form Otorisasi</h3>
                    </div>
                    <div class="panel-body">
                        <span id="form_result"></span>
                        {{-- PENGGUNA --}}
                        <div class="row mb-2">
                            <label class="col-md-2">PENGGUNA *</label>
                            <div class="col-md-6">
                                <select title="user" id="user" class="form-control">
                                    <option value="" selected hidden disabled>PILIH SALAH SATU</option>
                                    @foreach ($users as $item)
                                        <option value={{ $item->id }}>{{ $item->nik}} | {{ $item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <br>

                        {{-- ROLE --}}
                        <div class="row mb-2">
                            <label class="col-md-2">ROLE *</label>
                            <div class="col-md-6">
                                <select title="role" id="role" class="form-control">
                                    <option value="" selected hidden disabled>PILIH SALAH SATU</option>
                                    @foreach ($roles as $item)
                                        <option value={{ $item->reff1 }}>{{ $item->reff1 }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <br>

                        {{-- AUTHORITY --}}
                        <div class="row mb-2">
                            <label class="col-md-2">AUTORISASI *</label>
                            <div class="col-md-6">
                                <select title="authority" id="authority" class="form-control">
                                    <option value="" selected hidden disabled>PILIH SALAH SATU</option>
                                    @foreach ($authorities as $item)
                                        <option value={{ $item->reff1 }}>{{ $item->reff1 }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <br>

                        {{-- TANGGAL EFEKTIF --}}
                        <div class="row mb-2">
                            <label class="col-md-2">TANGGAL EFEKTIF *</label>
                            <div class="col-md-6">
                                <input maxlength="50" id="effective_date" type="text" class="text-uppercase form-control" name="effective_date" title="effective_date" value="{{$effective_date}}" disabled>
                            </div>
                        </div>
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

        var user      = $('#user').val();
        var role      = $('#role').val();
        var authority            = $('#authority').val();
        
        artLoadingDialogDo("Please wait, we process your request..",function(){
            $.ajax({
                url : '{!! route('masters/autorisation/create-new/create') !!}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{!!csrf_token()!!}'
                },
                dataType:"json",
                data: {
                    'user'     : user,
                    'role'     : role,
                    'authority'           : authority,
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
                        setTimeout(function(){ window.location.href = '{{url('masters/autorisation/index')}}'; }, 1500);
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
            return false;
        });
        return false;
    }

    function doBack(){
        setTimeout(function(){ window.location.href = '{{url('masters/autorisation/index')}}'; }, 100);
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