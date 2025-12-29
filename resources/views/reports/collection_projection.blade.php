@extends('layout.app')
@section('title')
    Collection Projection
@endsection
@section('pagetitle')
Collection Projection
@endsection
@section('content')
  <div class="container">
    <div class="row" id="advanced-input">
              <div class="col-md-12">
                <div class="panel widget">
                  <div class="panel-heading">
                  </div>
                  <div class="panel-body">
                    <div class="row">
                    <div class="col-md-7 col-lg-7 col-sm-12">
                      @include('includes.errors')
                  @include('includes.success')
                    </div>
                    </div>

                    <div id="chart" style="width: 100% height:400px"></div>
                  </div>
                </div>
                <!-- Panel Widget --> 
              </div>
              <!-- col-md-12 --> 
            </div>
            <!-- row -->
  </div>
@endsection
@section('scripts')
<script>
    Morris.Bar({
	  element: 'chart',
	  data: [
      {!!$collections!!}
    ],
	  xkey: 'month',
	  ykeys: ['due'],
	  labels: ['due'],
	  barColors: ["#23709E"]
	});
  
</script>
@endsection