<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Http\Traites\UserTraite;
use App\Models\Asset;
use App\Models\AssetType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetsController extends Controller
{
    use AuditTraite;
     use UserTraite;
     
    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function index(){
        return view('asset.index')->with('assets',Asset::orderBy('created_at','DESC')->get());
    }

    public function create(){
        return view('asset.create')->with('asstypes',AssetType::all());
    }

    public function store(Request $r) {
        $this->logInfo("creating asset",$r->all());
        
        $this->validate($r,[
            'asset_type' => ['required','string'],
            'date_purchased' => ['required','string'],
            'price' => ['required','string'],
            'replacement_value' => ['required','string'],
            'purchased_from' => ['required','string'],
            'note' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $aset = Asset::create([
            'user_id' => Auth::user()->id,
            'asset_type_id' => $r->asset_type,
            'branch_id' => $branch,
            'purchase_date' => $r->date_purchased,
            'purchase_price' => $r->price,
            'replacement_value' => $r->replacement_value,
            'initial' => $r->initial,
            'serial_number' => mt_rand('1111','9999'),
            'bought_from' => $r->purchased_from,
            'note' => $r->note
        ]);

        if($r->hasFile('file')){
            $filename = $r->file('file');
            $newfilename = time()."_".$filename->getClientOriginalName();
            $filename->move('uploads',$newfilename);

            Asset::where('id',$aset->id)->update([
                'file' => 'uploads/'.$newfilename
            ]);
        }

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'asset','created a new asset');

        return redirect()->route('assets.index')->with('success','Record Created');
    }

    public function edit($id){
        return view('asset.edit')->with('asstypes',AssetType::all())
                                ->with('ad', Asset::findorfail($id));
    }

    public function update(Request $r, $id){
        $this->logInfo("updating asset",$r->all());
        $this->validate($r,[
            'asset_type' => ['required','string'],
            'date_purchased' => ['required','string'],
            'price' => ['required','string'],
            'replacement_value' => ['required','string'],
            'purchased_from' => ['required','string'],
            'note' => ['required','string'],
        ]);

        $asset = Asset::findorfail($id);

        if($r->hasFile('file')){
            $filename = $r->file('file');
            $newfilename = time()."_".$filename->getClientOriginalName();
            $filename->move('uploads',$newfilename);

            $asset->file = 'uploads/'.$newfilename;
        }
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $asset->update([
            'user_id' => Auth::user()->id,
            'asset_type_id' => $r->asset_type,
            'branch_id' => $branch,
            'purchase_date' => $r->date_purchased,
            'purchase_price' => $r->price,
            'replacement_value' => $r->replacement_value,
            'initial' => $r->initial,
            'bought_from' => $r->purchased_from,
            'note' => $r->note
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'asset','updated an asset');

        return redirect()->route('assets.index')->with('success','Record Updated');

    }

    public function delete($id){
        Asset::findorfail($id)->delete();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'asset','deleted an asset');

        return redirect()->route('assets.index')->with('success','Record Deleted');
    }

    //asset type
    public function manage_asset_type(){
        return view('asset.manage_asset_type')->with('astyps',AssetType::orderBy('created_at','DESC')->get());
    }

    public function create_asset_type(){
        return view('asset.add_asset_type');
    }

    public function store_asset_type(Request $r){
        $this->logInfo("creating asset type",$r->all());
        
        $this->validate($r,[
            'name' => ['required','string'],
            'type' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        AssetType::create([
            'name' => $r->name,
            'type' => $r->type,
            'initial' => $r->initial
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'asset','created a new asset type');

        return redirect()->route('assetstyp.index')->with('success','Record Created');

    }

    public function edit_asset_type($id){
        return view('asset.edit_asset_type')->with('astyped',AssetType::findorfail($id));
    }

    public function update_asset_type(Request $r, $id){
        
        $this->logInfo("updating asset type",$r->all());
        
        $this->validate($r,[
            'name' => ['required','string'],
            'type' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        AssetType::findorfail($id)->update([
            'name' => $r->name,
            'type' => $r->type,
            'initial' => $r->initial
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'asset','updated an asset type');

        return redirect()->route('assetstyp.index')->with('success','Record Updated');
    }

    public function delete_asset_type($id){
      $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
        AssetType::findorfail($id)->delete();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'asset','deleted an asset type');

        return redirect()->route('assetstyp.index')->with('success','Record Deleted');
    }

}//endclass
