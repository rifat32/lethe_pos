@extends('layouts.app')

@section('title', 'POS')

@section('content')

<!-- Content Header (Page header) -->
<!-- <section class="content-header">
    <h1>Add Purchase</h1> -->
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
<!-- </section> -->
<input type="hidden" id="__precision" value="{{config('constants.currency_precision')}}">

<!-- Main content -->
{!! Form::open(['url' => action('SellPosController@update', [$transaction->id]), 'method' => 'post', 'id' => 'edit_pos_sell_form' ]) !!}

<section class="content no-print">
	<div class="row">
		<div class="col-sm-8">
			<div class="row" style=" margin-top: 5rem;">
                
				<div class=" col-sm-12 ">
                    <div class="form-group" style="width: 100% !important">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>


							@if($transaction->assistant)
							{{ $transaction->assistant->name }}
							<input type="hidden" name="assistant_id" value="{{$transaction->assistant->id}}"/>

							@else
						
							{!! Form::select('assistant_id',
                                [], null, ['class' => 'form-control mousetrap', 'id' => 'assistant_id', 'placeholder' => 'Enter Doctor Name / phone', 'style' => 'width: 100%;']); !!}
							@endif
                            {{-- {!! Form::select('doctor_id',
                                [], null, ['class' => 'form-control mousetrap', 'id' => 'doctor_id', 'placeholder' => 'Enter Doctor Name / phone', 'style' => 'width: 100%;']); !!} --}}
                            <span class="input-group-btn">
                                <a href="{{action('AssistantController@create')}}"  class="btn btn-default bg-white btn-flat add_new_doctor" data-name=""  @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></a>
                            </span>
                        </div>
                    </div>
                </div>
               
				@php
				$hide_tax = '';
				if( session()->get('business.enable_inline_tax') == 0){
					$hide_tax = 'hide';
				}
			@endphp
				<table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table3" style="background: white; margin-top: 1rem; margin-left:1rem;">
					<thead>
						<tr>
							<th class="tex-center col-md-4">
								@lang('sale.product') @show_tooltip(__('lang_v1.tooltip_sell_product_column'))
	
	
							</th>
							<th class="text-center col-md-3">
								@lang('sale.qty')
							</th>
							<th class="text-center col-md-2 {{$hide_tax}}">
								@lang('sale.price_inc_tax')
							</th>
							<th class="text-center col-md-3">
								@lang('sale.subtotal')
							</th>
							<th class="text-center col-md-3">
								Cost
							</th>
	
							<th class="text-center"><i class="fa fa-close" aria-hidden="true"></i></th>
						</tr>
					</thead>
					<tbody>
						<tbody>
							@foreach($sell_details as $sell_line)
							@if($sell_line->product->category_id == 30 && $sell_line->assistant_id)
								@include('sale_pos.product_row', ['product' => $sell_line, 'row_count' => $loop->index, 'tax_dropdown' => $taxes])
							
							@endif
								
							@endforeach
						</tbody>
					</tbody>
				</table>
            </div>
		</div>
	
		{{-- <div class="col-md-5 col-sm-12">
		@include('sale_pos.partials.right_div') 
		</div> --}}
	</div>
</section>
{!! Form::close() !!}

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog"
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog"
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
@stop
@section('javascript')
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
		{{-- <script src="{{ asset('js/posUpdated4.js?v=' . $asset_v) }}"></script> --}}
	<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
	@include('sale_pos.partials.keyboard_shortcuts')

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
@endsection

@section('css')
	<style type="text/css">
		/*CSS to print receipts*/
		.print_section{
		    display: none;
		}
		@media print{
		    .print_section{
		        display: block !important;
		    }
		}
		@page {
		    size: 3.1in auto;/* width height */
		    height: auto !important;
		    margin-top: 0mm;
		    margin-bottom: 0mm;
		}
	</style>
@endsection
