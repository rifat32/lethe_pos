@extends('layouts.app')
@section('title', 'Categories')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Assistants
        <small>Manage Assistant</small>
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
        	<h3 class="box-title">Manage Assistant</h3>
            @can('category.create')
        	<div class="box-tools">
                <a class="btn btn-block btn-primary"
                	href="{{action('AssistantController@create')}}">

                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
            </div>
            @endcan
        </div>
        <div class="box-body">
            @can('category.view')
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" >
        		<thead>
        			<tr>
        				<th>Name</th>
        				<th>Email</th>
                        <th>Phone</th>
                        {{-- <th>Commission</th> --}}
                       
                        <th>@lang( 'messages.action' )</th>
        			</tr>
        		</thead>

                <tbody>
                    @foreach ($doctors as  $doctor)
                   

                    <tr>
        				<td>{{$doctor->name}}</td>
        				<td>{{$doctor->email}}</td>
        				<td>{{$doctor->phone}}</td>
        				
                       
                        
        				<td>
                            <a href="{{route("assistants.history",['id' => $doctor->id])}}" class="btn btn-success">History</a>
                            <a href="{{route("assistants.edit",['id' => $doctor->id])}}" class="btn btn-primary">Edit</a>
                            <a href="{{route("assistants.delete",['id' => $doctor->id])}}" class="btn btn-danger">Delete</a>
                        </td>

        			</tr>
                    @endforeach
                </tbody>
        	</table>
            <div class="text-center">
                {{ $doctors->links() }}
                </div>
            </div>
            @endcan
        </div>
    </div>

    <div class="modal fade category_modal" tabindex="-1" role="dialog"
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
