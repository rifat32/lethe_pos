@extends('layouts.app')
@section('title', __('Check Warranty'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Warranty
        <small>Manage all Warranty List</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">Warranty List</h3>

        <form class="example" action="/search" method="post">
        <input type="hidden" name="_token" value="{{csrf_token()}}">
       <input type="text" placeholder="Search.." name="search">
      <button type="submit"><i class="fa fa-search"></i></button>
        </form>
            @if(auth()->user()->can('warranty_check.create'))
        	<div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('WarrantyCheckController@create')}}" 
                	data-container=".warranty_check_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
        
         
        </div>
        <div class="box-body">
        @if(auth()->user()->can('warranty_check.view'))
            <div class="table-responsive">
            @endif
            @if(session()->has('success'))
           <p class="alert alert-success" style="width:400px; height:25px; padding:0px;">
           {{ session()->get('success') }}
           </p>
             @endif
        	<table class="table table-bordered table-striped" id="warranty_check_table">
        		<thead>
        			<tr>
        				<th>Invoice ID</th>
        				<th>Warranty Issued Date</th>
                        <th>Duration(Year/s)</th>
                        <th>Expired Date</th>
                        <th>Actions</th>
        			</tr>
        		</thead>
                <tbody>
                    
                    	@foreach($warrants as $warrant)
                      <tr>
                           <td>{{ $warrant->invoice_id }}</td>
                           <td>{{ $warrant->warranty_issued_date }}</td>
                           
                           <td>{{ $warrant->duration }}</td> 
                           <td>{{ $warrant->expired_date }}</td>
                          
                          <td>
                          <a href="{{URL::to('edit_warranty/'.$warrant->id)}}"><button class="btn btn-outline-warning">Edit</button></a>
                            
                            <a onclick="return confirm('Delete this record?')"
                            href="{{URL::to('delete_warranty/'.$warrant->id)}}"><button class="btn btn-outline-danger">Delete</button></a>
                            
                          </td>
                      </tr>
                      @endforeach
        	</table>
            </div>
            {!! $warrants ->render() !!}
           @endif
        </div>
    </div>

    <div class="modal fade warranty_check_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
