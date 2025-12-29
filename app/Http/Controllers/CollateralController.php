<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Http\Traites\UserTraite;
use App\Models\Collateral;
use App\Models\CollateralType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollateralController extends Controller
{
    use AuditTraite;
    use UserTraite;
    
    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function index()
    {
        $data = Collateral::all();

        return view('collateral.index')->with('data',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('collateral.create')->with('types',CollateralType::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         $this->logInfo("creating colleteral",$request->all());
         
        $colat = Collateral::firstOrCreate([
            'collateral_type_id' => $request->collateral_type_id,
            'name' => $request->name,
            'loan_id' => $request->loanid,
            'customer_id' => $request->customerid,
            'value' => $request->value,
            'status' => $request->status,
            'serial_number' => $request->serial_number,
            'model_name' => $request->model_name,
            'model_number' => $request->model_number,
            'manufacture_date' => $request->manufacture_date,
            'date' => $request->date,
            'notes' => $request->notes,
        ]);
       
        if($request->hasFile('photo')){
            $photo = $request->file('photo');
                $newfilephoto = time()."_".$photo->getClientOriginalName();
                $photo->move('uploads',$newfilephoto);
                
            Collateral::where('id',$colat->id)->update([
                'photo' => 'uploads/'.$newfilephoto
            ]);
        }

        if($request->hasFile('files')){
            $filesarray = array();
            $files = $request->file('files');
               foreach($files as $file){
                $newfilefile = time()."_".$file->getClientOriginalName();
                $file->move('uploads',$newfilefile);
                $filesarray[] = 'uploads/'.$newfilephoto;
               }
                
            Collateral::where('id',$colat->id)->update([
                'files' => $filesarray
            ]);
        }
       
     $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'collateral','Added collateral  with id:' .$colat->id);

        return redirect($request->return_url)->with('success','collateral added');
    }


    public function show($id)
    {
        return view('collateral.show')->with('collateral',Collateral::findorfail($id));
    }


    public function edit($id)
    {
        return view('collateral.edit')->with('ed',Collateral::findorfail($id))
                                      ->with('types',CollateralType::all());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $this->logInfo("updating colleteral",$request->all());
                 
     $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        $collateral = Collateral::findorfail($id);

        if($request->hasFile('photo')){
            $photo = $request->file('photo');
                $newfilephoto = time()."_".$photo->getClientOriginalName();
                $photo->move('uploads',$newfilephoto);

                $collateral->photo = 'uploads/'.$newfilephoto;
        }

        if($request->hasFile('files')){
            $filesarray = array();

            $files = $request->file('files');

            foreach((array)$collateral->files as $f){
                if(file_exists($f)){
                    unlink($f);
                }
            }
               foreach($files as $file){
                $newfile = time()."_".$file->getClientOriginalName();
                $file->move('uploads',$newfile);
                $filesarray[] = 'uploads/'.$newfile;
               }
               $collateral->files = $filesarray;
        }

        $collateral->collateral_type_id = $request->collateral_type_id;
        $collateral->name = $request->name;
        $collateral->value = $request->value;
        $collateral->status = $request->status;
        $collateral->serial_number = $request->serial_number;
        $collateral->model_name = $request->model_name;
        $collateral->model_number = $request->model_number;
        $collateral->manufacture_date = $request->manufacture_date;
        $collateral->date = $request->date;
        $collateral->notes = $request->notes;
        $collateral->updated_at = Carbon::now();

        $collateral->save();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'collateral','Updated collateral  with id:' .$id);
       
        if (!empty($request->return_url)) {
            return redirect($request->return_url)->with('success','collateral updated');
        }
        return redirect()->route('colla.index')->with('success','collateral updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
      $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        $cala = Collateral::findorfail($id);
 
        if(file_exists($cala->photo)){
            unlink($cala->photo);
        }
        
        foreach((array)$cala->files as $f){
            if(file_exists($f)){
                unlink($f);
            }
        }
        
        $cala->delete();
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'collateral','deleted collateral  with id:' .$id);
       
        return redirect()->back()->with('success','collateral deleted');
    }

    public function deleteFile(Request $request, $id)
    {
       
        $collateral = Collateral::findorfail($id);
        foreach((array)$collateral->files as $f){
            if(file_exists($f) && $request->filid == $f){
                unset($f);
                unlink($f);
            }
        }
        return redirect()->back()->with('success','File deleted');
        // $collateral->save();
    }

    //expense type
    public function collateral_type_index()
    {
        $data = CollateralType::all();

        return view('collateral.type.index')->with('data',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function collateral_type_create()
    {

        return view('collateral.type.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function collateral_type_store(Request $request)
    {
        $this->logInfo("creating colleteral type",$request->all());

        CollateralType::firstOrCreate([
            'name' => $request->name
        ]);
        return redirect()->route('collatype.index')->with('success','collateral type added');
    }

    public function collateral_type_edit($id)
    {
        return view('collateral.type.edit')->with('ed',CollateralType::findorfail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function collateral_type_update(Request $request, $id)
    {
        $this->logInfo("updating colleteral type",$request->all());

        $type = CollateralType::findorfail($id);
        $type->name = $request->name;
        $type->save();

        return redirect()->route('collatype.index')->with('success','collateral type updated');

    }

    public function collateral_type_delete($id)
    {
        CollateralType::findorfail($id)->delete();

        return redirect()->route('collatype.index')->with('success','collateral type deleted');

    }
}
