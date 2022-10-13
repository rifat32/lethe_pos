@extends('layouts.app')
@section('title', __('Supplier Warranty'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Supplier Warranty
        <small>Manage Your Supplier Warranty</small>
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
        	<h3 class="box-title">All your Supplier Warrnty</h3>
            @if(auth()->user()->can('supplier_warranty.create'))
        	<div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('SupplierWarrantyController@create')}}" 
                	data-container=".supplier_warranty_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
            @endif
        </div>
        <div class="box-body">
        @if(auth()->user()->can('supplier_warranty.view'))
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" id="supplier_warranty_table">
        		<thead>
        			<tr>
                        <th>Warrent ID</th>
        				<th>Customer</th>
        				<th>Product</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
        			</tr>
        		</thead>
        	</table>
            </div>
            @endif
        </div>
    </div>

    <div class="modal fade supplier_warranty_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
