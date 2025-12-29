<?php
namespace App\Http\Traites;

use App\Models\Audittrail;
use App\Models\Branch;

trait AuditTraite{
    public function tracktrails($userid,$branchid,$usernme,$modle,$nte){
        Audittrail::create([
            'user_id' => $userid,
            'branch_id' => $branchid,
            'user' => $usernme,
            'module' => $modle,
            'notes' => $nte
        ]);
    }
}