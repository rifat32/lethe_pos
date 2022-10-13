@extends('layouts.app')
@section('title', __('Kitchen Dish List'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Dish List
        <small>Manage Your Dishes</small>
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
        	<h3 class="box-title">All your dishes</h3>
            @if(auth()->user()->can('ikdlist.create'))
        	<div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('Restaurant\DishListController@create')}}" 
                	data-container=".dish_list_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
            @endif
        </div>
        <div class="box-body">
        @if(auth()->user()->can('ikdlist.view'))
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" id="dish_list_table">
        		<thead>
        			<tr>
        				<th>Dish Name</th>
        				<th>Category</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Availability</th>
                        <th>Actions</th>
        			</tr>
        		</thead>
        	</table>
            </div>
            @endif
        </div>
    </div>
    <div class="modal fade dish_list_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
