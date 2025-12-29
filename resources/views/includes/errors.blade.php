@if ($errors->any())
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="icon-cross"></i></button>
    <ul  class="list-unstyled">
           @foreach ($errors->all() as $error)
          <li>{{$error}}</li>
      @endforeach
    </ul>
    </div>
@endif

@if (session()->has('error'))
<div class="alert alert-danger">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="icon-cross"></i></button>
    <strong><i class="fa fa-exclamation"></i> {{session()->get('error')}}</strong>
</div>   
@endif