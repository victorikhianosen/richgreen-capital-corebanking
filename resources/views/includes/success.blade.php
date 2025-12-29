@if (session()->has('success'))
<div class="alert alert-success">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="icon-cross"></i></button>
    <strong><i class="fa fa-check"></i> {{session()->get('success')}}</strong>
</div>   
@endif