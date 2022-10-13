@extends('layouts.app')
@section('title', __('Kitchen Raw Items'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Raw Items
        <small>Manage Your Raw Items</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">

	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">List of Raw Items</h3>
            @if(auth()->user()->can('ikitem.create'))
        	<div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('Restaurant\InternalKitchenController@create')}}" 
                	data-container=".raw_item_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
            @endif
        </div>
        <div class="box-body">
        @if(auth()->user()->can('ikitem.view'))
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" id="raw_item_table">
        		<thead>
        			<tr>
        				<th>Name</th>
        				<th>Quantity</th>
                        <th>Unit</th>
        				<th>Unit Price</th>
                        <th>Will be Used As</th>
                        <th>Total Price</th>
                        <th>@lang( 'messages.action' )</th>
        			</tr>
        		</thead>
        	</table>
            </div>
            @endif
        </div>
    </div>

    <div class="modal fade raw_item_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
