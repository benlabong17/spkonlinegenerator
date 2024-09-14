<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Carbon\Carbon;
use App\Models\Basecamp;
use App\Models\Department;
use App\Models\BasecampHist;
use Illuminate\Http\Request;
use App\Models\DepartmentHist;
use App\Exports\DepartmentExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\Schema\Blueprint;

class BasecampController extends Controller
{
    public function index()
    {
        return view('masters.basecamp.basecamp_index');
    }

    public function getData($request, $isExcel = '')
    {

        if ($isExcel == "") {
            session([
                'basecamp' . '.basecamp_name' => $request->has('basecamp_name') ?  $request->input('basecamp_name') : '',
                'basecamp' . '.status' => $request->has('status') ?  $request->input('status') : '',
            ]);
        }

        $basecamp_name  = session('basecamp' . '.basecamp_name') != '' ? session('basecamp' . '.basecamp_name') : '';
        $status           = session('basecamp' . '.status') != '' ? session('basecamp' . '.status') : '';

        $basecamp_name  = strtoupper($basecamp_name);
        $status           = strtoupper($status);

        $basecampDatas = Basecamp::where('active', $status);

        if ($basecamp_name != '') {
            $basecampDatas = $basecampDatas->where('basecamp', $basecamp_name);
        }

        return $basecampDatas;
    }

    public function data(Request $request)
    {
        $datas = $this->getData($request);

        $datatables = DataTables::of($datas)
            ->filter(function ($instance) use ($request) {
                return true;
            });

        $datatables = $datatables->addColumn('action', function ($item) use ($request) {
            $txt = '';
            $txt .= "<a href=\"#\" onclick=\"showItem('$item[id]');\" title=\"" . ucfirst(__('view')) . "\" class=\"btn btn-xs btn-secondary\"><i class=\"fa fa-eye fa-fw fa-xs\"></i></a>";
            // $txt .= "<a href=\"#\" onclick=\"editItem($item[id]);\" title=\"" . ucfirst(__('edit')) . "\" class=\"btn btn-xs btn-secondary\"><i class=\"fa fa-edit fa-fw fa-xs\"></i></a>";
            $txt .= "<a href=\"#\" onclick=\"deleteItem($item[id]);\" title=\"" . ucfirst(__('delete')) . "\" class=\"btn btn-xs btn-secondary\"><i class=\"fa fa-trash fa-fw fa-xs\"></i></a>";

            return $txt;
        })
            ->addColumn('active', function ($item) {
                if ($item->active == 1) {
                    return 'YA';
                } else {
                    return 'TIDAK';
                }
            })
            ->editColumn('start_effective', function ($item) {
                return Carbon::createFromFormat("Y-m-d H:i:s", $item->start_effective)->format('d/m/Y');
            })
            ->editColumn('end_effective', function ($item) {
                if ($item->end_effective == null) {
                    return '-';
                } else {
                    return Carbon::createFromFormat("Y-m-d H:i:s", $item->end_effective)->format('d/m/Y');
                }
            })
            ->addColumn('created_by', function ($item) {
                return optional($item->createdBy)->name;
            })
            ->editColumn('created_at', function ($item) {
                return Carbon::createFromFormat("Y-m-d H:i:s", $item->created_at)->format('d/m/Y H:i:s');
            })
            ->addColumn('updated_by', function ($item) {
                return optional($item->updatedBy)->name;
            })
            ->editColumn('updated_at', function ($item) {
                return Carbon::createFromFormat("Y-m-d H:i:s", $item->updated_at)->format('d/m/Y H:i:s');
            });

        return $datatables->make(TRUE);
    }

    public function createNew()
    {
        return view('masters.basecamp.form_input');
    }

    public function submitData(Request $request)
    {
        $basecampName = strtoupper($request->basecamp_name);
        $description = strtoupper($request->description);

        if ($basecampName == '') {
            return response()->json([
                'errors' => true,
                "message" => '<div class="alert alert-danger">Nama basecamp wajib terisi, harap periksa kembali formulir pengisian data</div>'
            ]);
        }

        if ($description == '') {
            return response()->json([
                'errors' => true,
                "message" => '<div class="alert alert-danger">Deskripsi wajib terisi, harap periksa kembali formulir pengisian data</div>'
            ]);
        }

        $checkDuplicateData = Basecamp::where('basecamp', $basecampName)
            ->where('active', 1)
            ->first();

        if ($checkDuplicateData) {
            return response()->json([
                'errors' => true,
                "message" => '<div class="alert alert-danger">Telah ditemukan data basecamp ' . $basecampName . ' yang masih aktif</div>'
            ]);
        }

        try {
            // CREATE DATA 
            DB::beginTransaction();

            $insertBasecamp = new Basecamp([
                'basecamp'              => $basecampName,
                'basecamp_description'  => $description,
                'active'                => 1,
                'start_effective'       => Carbon::now()->timezone('Asia/Jakarta'),
                'end_effective'         => null,
                'created_by'            => Auth::user()->id,
                'created_at'            => Carbon::now()->timezone('Asia/Jakarta'),
                'updated_by'            => Auth::user()->id,
                'updated_at'            => Carbon::now()->timezone('Asia/Jakarta'),
            ]);
            $insertBasecamp->save();

            $insertbasecampHist = new BasecampHist([
                'basecamp_id'           => $insertBasecamp->id,
                'basecamp'              => $insertBasecamp->basecamp,
                'basecamp_description'  => $insertBasecamp->basecamp_description,
                'active'                => $insertBasecamp->active,
                'start_effective'       => $insertBasecamp->start_effective,
                'end_effective'         => $insertBasecamp->end_effective,
                'action'                => 'CREATE',
                'created_by'            => Auth::user()->id,
                'created_at'            => Carbon::now()->timezone('Asia/Jakarta'),
            ]);
            $insertbasecampHist->save();

            DB::commit();
            return response()->json([
                'success' => true,
                "message" => '<div class="alert alert-success">Data berhasil disimpan</div>'
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'errors' => true,
                "message" => '<div class="alert alert-danger">Telah terjadi kesalahan sistem, data gagal diproses</div>'
            ]);
        }
    }

    public function deleteData(Request $request)
    {
        $basecamp = Basecamp::where('id', $request->id)->first();

        try {
            DB::beginTransaction();
            if ($basecamp) {
                $basecamp->active         = 0;
                $basecamp->end_effective  = Carbon::now()->timezone('Asia/Jakarta');
                $basecamp->updated_by     = Auth::user()->id;
                $basecamp->updated_at     = Carbon::now()->timezone('Asia/Jakarta');
                $basecamp->save();

                $insertbasecampHist = new BasecampHist([
                    'basecamp_id'           => $basecamp->id,
                    'basecamp'              => $basecamp->basecamp,
                    'basecamp_description'  => $basecamp->basecamp_description,
                    'active'                => $basecamp->active,
                    'start_effective'       => $basecamp->start_effective,
                    'end_effective'         => $basecamp->end_effective,
                    'action'                => 'UPDATE',
                    'created_by'            => Auth::user()->id,
                    'created_at'            => Carbon::now()->timezone('Asia/Jakarta'),
                ]);
                $insertbasecampHist->save();

                DB::commit();
                return response()->json([
                    'success' => true,
                    "message" => '<div class="alert alert-success">Data basecamp berhasil dihapus, status : TIDAK AKTIF</div>'
                ]);
            } else {
                return response()->json([
                    'errors' => true,
                    "message" => '<div class="alert alert-danger">Data gagal di proses, data basecamp tidak ditemukan</div>'
                ]);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'errors' => true,
                "message" => '<div class="alert alert-danger">Data gagal di proses, terjadi kesalah system</div>'
            ]);
        }
    }

    public function detailData($id)
    {
        $basecamp = Basecamp::where('id', $id)->first();
        if ($basecamp) {
            if ($basecamp->active == 1) {
                $active = 'AkTIF';
            } else {
                $active = 'TIDAK AkTIF';
            }
            return view('masters.basecamp.form_detail', [
                'basecamp'             => $basecamp->basecamp,
                'basecamp_description' => $basecamp->basecamp_description != '' ? $basecamp->basecamp_description : '-',
                'active'               => $active,
                'start_effective'      => $basecamp->start_effective != '' ? Carbon::createFromFormat('Y-m-d H:i:s', $basecamp->start_effective)->format('d/m/Y H:i:s') : '-',
                'end_effective'        => $basecamp->end_effective != '' ? Carbon::createFromFormat('Y-m-d H:i:s', $basecamp->end_effective)->format('d/m/Y H:i:s') : '-',
                'created_by'           => optional($basecamp->createdBy)->name,
                'created_at'           => $basecamp->created_at != '' ? Carbon::createFromFormat('Y-m-d H:i:s', $basecamp->created_at)->format('d/m/Y H:i:s') : '-',
                'updated_by'           => optional($basecamp->updatedBy)->name,
                'updated_at'           => $basecamp->updated_at != '' ? Carbon::createFromFormat('Y-m-d H:i:s', $basecamp->updated_at)->format('d/m/Y H:i:s') : '-',
            ]);
        } else {
            return view('masters.basecamp.form_detail', [
                'basecamp'             => '',
                'basecamp_description' => '',
                'active'               => '',
                'start_effective'      => '',
                'end_effective'        => '',
                'created_by'           => '',
                'created_at'           => '',
                'updated_by'           => '',
                'updated_at'           => '',
            ]);
        }
    }

    public function importExcel()
    {
        return view('masters.basecamp.upload');
    }

    public function makeTempTable()
    {
        Schema::create('temp', function (Blueprint $table) {
            $table->increments('id');
            $table->string('basecamp', 50)->nullable();
            $table->string('description', 255)->nullable();
            $table->text('remark')->default('');
            $table->temporary();
        });
    }

    public function dropTempTable()
    {
        Schema::dropIfExists('temp');
    }

    public function uploadDepartment(Request $request)
    {
        $countError = 0;
        $success   = false;
        if ($request->hasfile('validatedCustomFile')) {
            $name = $request->file('validatedCustomFile')->getClientOriginalName();
            $filename = $name;
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if (strtolower($ext) != 'xlsx') {
                $filename = "";
                $message = '<div class="alert alert-danger">format file tidak sesuai</div>';
                // $error    = ucfirst(__('format file tidak sesuai'));
                $success    = false;
                return response()->json([
                    'filename'    => $filename,
                    'message'    => $message,
                    'success'    => $success,
                ]);
            }

            $extension = $request->file('validatedCustomFile')->getClientOriginalExtension();

            $name = "Basecamp" . "_" . Auth::user()->id . "." . $extension;
            $request->file('validatedCustomFile')->move(storage_path() . '/app/uploads/', $name);
            $attachments = storage_path() . '/app/uploads/' . $name;
            
            $data = (new FastExcel)->import($attachments);

            foreach ($data as $row) {
                $error = 0;

                $basecamp        = trim(strtoupper($row['Basecamp']), ' ');
                $description     = trim(strtoupper($row['Deskripsi']), ' ');

                if($basecamp == '' && $description == ''){
                    continue;
                }

                $checkDuplicateData = Basecamp::where('basecamp', $basecamp)
                                                ->where('active', 1)
                                                ->first();

                if ($checkDuplicateData) {
                    $error++;
                }

                if ($description == '') {
                    $error++;
                }

                if ($error > 0) {
                    $countError++;
                }
            }

            if ($countError > 0) {
                $success   = false;
                $message   = '<div class="alert alert-danger">Terdapat data error, harap periksa kembali file ' . $filename . '</div>';
            } else {
                $success   = true;
                $message   = '<div class="alert alert-success">Validasi data berhasil, data dapat disimpan</div>';
            }

            return response()->json([
                'filename'  => $attachments,
                'success'  => $success,
                'message'  => $message,
            ]);
        } else {
            $message   = '<div class="alert alert-danger">Pilih file...</div>';
            return response()->json([
                'filename'  => '',
                'success'  => $success,
                'message'  => $message,
            ]);
        }
    }

    public function displayUpload(Request $request)
    {
        $this->dropTempTable();
        $this->makeTempTable();
        
        if ($request->fileName != "" || $request->fileName != null) {
            $attachments = $request->fileName;
            $data = (new FastExcel)->import($attachments);

            $countError = 0;
            $tempData = [];
            foreach ($data as $row) {
                $remark = [];

                $basecamp        = trim(strtoupper($row['Basecamp']), ' ');
                $description     = trim(strtoupper($row['Deskripsi']), ' ');

                if($basecamp == '' && $description == ''){
                    continue;
                }

                $checkDuplicateData = Basecamp::where('basecamp', $basecamp)
                                                ->where('active', 1)
                                                ->first();

                if ($checkDuplicateData) {
                    $remark [] = 'Terdapat Basecamp '.$basecamp.' yang masih aktif';
                }

                if ($description == '') {
                    $remark [] = 'Deskripsi tidak boleh kosong';
                }

                if (count($remark) > 0) {
                    $countError++;
                }

                $tempOutput = [
                    'basecamp'     => $basecamp,
                    'description'    => $description,
                    'remark'         => implode(', ', $remark)
                ];
                DB::table('temp')->insert($tempOutput);
                $tempData = DB::table('temp')->get();
            }
            if(count($tempData) == 0){
                $tempOutput = [
                    'basecamp'       => '',
                    'description'    => '',
                    'remark'         => ''
                ];
                DB::table('temp')->insert($tempOutput);
                $tempData = DB::table('temp')->get();
            }
        } else {
            $tempOutput = [
                'basecamp'       => '',
                'description'    => '',
                'remark'         => ''
            ];
            DB::table('temp')->insert($tempOutput);
            $tempData = DB::table('temp')->get();
        }

        $datatables = Datatables::of($tempData)
            ->filter(function ($instance) use ($request) {
                return true;
            });

        return $datatables->make(TRUE);
    }

    public function saveUpload(Request $request)
    {
        if($request->fileData != "")
        {
            $attachments = $request->fileData;  
            $data = (new FastExcel)->import($attachments);
            
            try {
                DB::beginTransaction();
                foreach ($data as $row) {
                    $error = false;

                    $basecamp        = trim(strtoupper($row['Basecamp']), ' ');
                    $description     = trim(strtoupper($row['Deskripsi']), ' ');

                    if($basecamp == '' && $description == ''){
                        continue;
                    }

                    $checkDuplicateData = Basecamp::where('basecamp', $basecamp)
                                                ->where('active', 1)
                                                ->first();

                    if ($checkDuplicateData) {
                        $error = true;
                    }

                    if ($description == '') {
                        $error = true;
                    }

                    if (!$error){
                        $insertBasecamp = new Basecamp([
                            'basecamp'              => $basecamp,
                            'basecamp_description'  => $description,
                            'active'                => 1,
                            'start_effective'       => Carbon::now()->timezone('Asia/Jakarta'),
                            'end_effective'         => null,
                            'created_by'            => Auth::user()->id,
                            'created_at'            => Carbon::now()->timezone('Asia/Jakarta'),
                            'updated_by'            => Auth::user()->id,
                            'updated_at'            => Carbon::now()->timezone('Asia/Jakarta'),
                        ]);
                        $insertBasecamp->save();
            
                        $insertbasecampHist = new BasecampHist([
                            'basecamp_id'           => $insertBasecamp->id,
                            'basecamp'              => $insertBasecamp->basecamp,
                            'basecamp_description'  => $insertBasecamp->basecamp_description,
                            'active'                => $insertBasecamp->active,
                            'start_effective'       => $insertBasecamp->start_effective,
                            'end_effective'         => $insertBasecamp->end_effective,
                            'action'                => 'CREATE',
                            'created_by'            => Auth::user()->id,
                            'created_at'            => Carbon::now()->timezone('Asia/Jakarta'),
                        ]);
                        $insertbasecampHist->save();
                    } else {
                        DB::rollback();

                        $success   = false;
                        $message   = '<div class="alert alert-danger">Terdapat data error, harap periksa kembali file</div>';

                        return response()->json([
                            'success'  => $success,
                            'message'  => $message,
                        ]);
                    }
                }
                DB::commit();

                $success   = true;
                $message   = '<div class="alert alert-success">File berhasil diproses</div>';
                return response()->json([
                    'success'  => $success,
                    'message'  => $message,
                ]);
            } catch(\Exception $e){
                DB::rollback();
                $success   = false;
                $message   = '<div class="alert alert-danger">Terdapat kesalahn, harap proses kembali</div>';

                return response()->json([
                    'success'  => $success,
                    'message'  => $message,
                ]);
            }
        } else {
            $success   = false;
            $message   = '<div class="alert alert-danger">File tidak ditemukan, harap periksa kembali</div>';

            return response()->json([
                'success'  => $success,
                'message'  => $message,
            ]);
        }
    }

    public function downloadDepartmentTemplate()
    {
        $filename = 'Template_Master_Basecamp.xlsx';
        return response()->download(storage_path('app/files/' . $filename));
    }
}
