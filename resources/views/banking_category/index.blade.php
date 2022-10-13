@extends('layouts.app')
@section('title', __('expense.expense_categories'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Banking Categories
        <small>Manage Your Banking Categories</small>
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
        	<h3 class="box-title">All your banking categories</h3>
            @if(auth()->user()->can('ibcategory.create'))
        	<div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('BankingCategoryController@create')}}" 
                	data-container=".banking_category_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
            @endif
        </div>
        <div class="box-body">
        @if(auth()->user()->can('ibcategory.view'))
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" id="banking_category_table">
        		<thead>
        			<tr>
        				<th>@lang( 'expense.category_name' )</th>
        				<th>@lang( 'expense.category_code' )</th>
                        <th>@lang( 'messages.action' )</th>
        			</tr>
        		</thead>
        	</table>
            </div>
            @endif
        </div>
    </div>

    <div class="modal fade banking_category_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
