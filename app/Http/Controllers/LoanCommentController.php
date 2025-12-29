<?php

namespace App\Http\Controllers;

use App\Models\LoanComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanCommentController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function create()
    {
        return view('loan.comment.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       LoanComment::create([
        'notes' => $request->notes,
        'user_id' => Auth::user()->id,
        'loan_id' => $request->loanid,
       ]);

        return redirect($request->return_url)->with('success', 'comment added');
    }


    public function edit($id)
    {
        return view('loan.comment.edit')->with('ed',LoanComment::findorfail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $loan_comment = LoanComment::findorfail($id);
        $loan_comment->notes = $request->notes;
        $loan_comment->save();

        return redirect($request->return_url)->with('success','comment updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        LoanComment::findorfail($id)->delete();

        return redirect()->back()->with('success','comment deleted');
    }
}
