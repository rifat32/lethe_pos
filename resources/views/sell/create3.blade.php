@extends('layouts.app')

@section('title', __('sale.add_sale'))

@section('content')
<input type="hidden" id="__precision" value="{{config('constants.currency_precision')}}">
<!-- Content Header (Page header) -->

<!-- Main content -->
{!! Form::open(['url' => action('SellPosController@store'), 'method' => 'post', 'id' => 'add_sell_form' ]) !!}
<section class="content no-print" style="display: none;">
@if(is_null($default_location))
<div class="row">
	<div class="col-sm-3">
		<div class="form-group">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-map-marker"></i>
				</span>
			{!! Form::select('select_location_id', $business_locations, null, ['class' => 'form-control input-sm', 
			'placeholder' => __('lang_v1.select_location'),
			'id' => 'select_location_id', 
			'required', 'autofocus'], $bl_attributes); !!}
			<span class="input-group-addon">
					@show_tooltip(__('tooltip.sale_location'))
				</span> 
			</div>
		</div>
	</div>
</div>
@endif
<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">

    <input type="hidden" name="order_id" value="{{$order->id}}}"/>
	<div class="row">
		<div class="col-md-12 col-sm-12">
			<div class="box box-solid">
				{!! Form::hidden('location_id', $default_location, ['id' => 'location_id', 'data-receipt_printer_type' => isset($bl_attributes[$default_location]['data-receipt_printer_type']) ? $bl_attributes[$default_location]['data-receipt_printer_type'] : 'browser']); !!}

				<!-- /.box-header -->
				<div class="box-body">
					@if(!empty($price_groups))
						@if(count($price_groups) > 1)
							<div class="col-sm-4">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">
											<i class="fa fa-money"></i>
										</span>
										@php
											reset($price_groups);
										@endphp
										{!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
										{!! Form::select('price_group', $price_groups, null, ['class' => 'form-control select2', 'id' => 'price_group']); !!}
										<span class="input-group-addon">
											@show_tooltip(__('lang_v1.price_group_help_text'))
										</span> 
									</div>
								</div>
							</div>
							<div class="clearfix"></div>
						@else
							@php
								reset($price_groups);
							@endphp
							{!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
						@endif
					@endif
					<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
						<div class="form-group">
							{!! Form::label('contact_id', __('contact.customer') . ':*') !!}
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fa fa-user"></i>
								</span>
								<input type="hidden" id="default_customer_id" 
								value="{{ $walk_in_customer['id']}}" >
								<input type="hidden" id="default_customer_name" 
								value="{{ $walk_in_customer['name']}}" >
								{!! Form::select('contact_id', 
									[], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required']); !!}
								<span class="input-group-btn">
									<button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
								</span>
							</div>
						</div>
					</div>

					<div class="col-md-3">
			          <div class="form-group">
			            <div class="multi-input">
			              {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
			              <br/>
			              {!! Form::number('pay_term_number', $walk_in_customer['pay_term_number'], ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); !!}

			              {!! Form::select('pay_term_type', 
			              	['months' => __('lang_v1.months'), 
			              		'days' => __('lang_v1.days')], 
			              		$walk_in_customer['pay_term_type'], 
			              	['class' => 'form-control width-60 pull-left','placeholder' => __('messages.please_select')]); !!}
			            </div>
			          </div>
			        </div>

					@if(!empty($commission_agent))
					<div class="col-sm-3">
						<div class="form-group">
						{!! Form::label('commission_agent', __('lang_v1.commission_agent') . ':') !!}
						{!! Form::select('commission_agent', 
									$commission_agent, null, ['class' => 'form-control select2']); !!}
						</div>
					</div>
					@endif
					<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
						<div class="form-group">
							{!! Form::label('transaction_date', __('sale.sale_date') . ':*') !!}
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fa fa-calendar"></i>
								</span>
								{!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']); !!}
							</div>
						</div>
					</div>
                    <input type="hidden" name="doctor_id" value=""/>
					<input type="hidden" name="assistant_id" value=""/>
					<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
						<div class="form-group">
							{!! Form::label('status', __('sale.status') . ':*') !!}
							{!! Form::select('status', ['final' => __('sale.final'), 'draft' => __('sale.draft'), 'quotation' => __('lang_v1.quotation')], "final", ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
						</div>
					</div>
					
				</div>
				<!-- /.box-body -->
			</div>
			<!-- /.box -->
			<div class="box box-solid">
				<div class="box-body">
					<div class="col-sm-10 col-sm-offset-1">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fa fa-barcode"></i>
								</span>
								{!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
								'disabled' => is_null($default_location)? true : false,
								'autofocus' => is_null($default_location)? false : true,
								]); !!}
							</div>
						</div>
					</div>

					<div class="row col-sm-12 pos_product_div" style="min-height: 0">

						<input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">

						<!-- Keeps count of product rows -->
						<input type="hidden" id="product_row_count" 
							value="0">
						@php
							$hide_tax = '';
							if( session()->get('business.enable_inline_tax') == 0){
								$hide_tax = 'hide';
							}
						@endphp
					<div class="table-responsive">
						<table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
							<thead>
								<tr>
									<th class="text-center">	
										Product									</th>
									<th class="text-center">
										Quantity									</th>
									<th class="text-center hide">
										Price inc. tax									</th>
									<th class="text-center">
										Subtotal									</th>
									<th class="text-center"><i class="fa fa-close" aria-hidden="true"></i></th>
								</tr>
							</thead>
							<tbody>

{{-- AAAAAAAAAAAAAAAAAAAAA --}}

@foreach ($order->orderDetails as $key => $orderDetail)
<tr class="product_row" data-row_index="{{$key}}">
	<td>
		
				<div data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Edit product Unit Price and Tax">
		<span class="text-link text-info cursor-pointer" data-toggle="modal" data-target="#row_edit_product_price_modal_3">
			{{ $orderDetail->product->product->name }}
			&nbsp;<i class="fa fa-info-circle"></i>
		</span>
		</div>
				<input type="hidden" class="enable_sr_no" value="{{ $orderDetail->product->product->enable_sr_no }}">
		{{-- <div data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Add Description">
			<i class="fa fa-commenting cursor-pointer text-primary add-pos-row-description" data-toggle="modal" data-target="#row_description_modal_3"></i>
		</div> --}}

		
		 <div class="modal fade row_edit_product_price_model" id="row_edit_product_price_modal_3" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			<h4 class="modal-title" id="myModalLabel">{{ $orderDetail->product->product->name }}</h4>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="form-group col-xs-12 ">
					<label>Unit Price</label>
						<input type="text" name="products[{{$key}}][unit_price]" class="form-control pos_unit_price input_number mousetrap" value="{{ $orderDetail->price/$orderDetail->quantity }}">
                        <input type="hidden" name="products[{{$key}}][cost]" class="form-control" value="0">
                        <input type="hidden" name="products[{{$key}}][category]" class="form-control" value={{ $orderDetail->product->product->category_id }}>
                        
				</div>
								<div class="form-group col-xs-12 col-sm-6 ">
					<label>Discount Type</label>
						<select class="form-control row_discount_type" name="products[{{$key}}][line_discount_type]"><option value="fixed" selected="selected">Fixed</option><option value="percentage">Percentage</option></select>
				</div>
				<div class="form-group col-xs-12 col-sm-6 ">
					<label>Discount Amount</label>
						<input class="form-control input_number row_discount_amount" name="products[{{$key}}][line_discount_amount]" type="text" value="0.00">
				</div>
				<div class="form-group col-xs-12 hide">
					<label>Tax</label>

					<input class="item_tax" name="products[{{$key}}][item_tax]" type="hidden" value="0.00">
		
					<select class="form-control tax_id" name="products[{{$key}}][tax_id]"><option selected="selected" value="">Select</option><option value="" selected="selected">None</option></select>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	</div>
</div>		</div> 

		<!-- Description modal start -->
		<div class="modal fade row_description_modal" id="row_description_modal_3" tabindex="-1" role="dialog">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		        <h4 class="modal-title" id="myModalLabel">Cute Cartoon Rabbit Print Quick Release Buckle Cat Necklace Collar - 0753</h4>
		      </div>
		      <div class="modal-body">
		      	<div class="form-group">
		      		<label>Description</label>
		      				      		<textarea class="form-control" name="products[{{$key}}][sell_line_note]" rows="3"></textarea>
		      		<p class="help-block">Add product IMEI, Serial number or other informations here.</p>
		      	</div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		      </div>
		    </div>
		  </div>
		</div>
		<!-- Description modal end -->
		
		
									

			
	</td>

	<td>
		
		
		<input type="hidden" name="products[{{$key}}][product_id]" class="form-control product_id" value="{{$orderDetail->product->product->id}}">

		<input type="hidden" value="{{ $orderDetail->product->id }}" name="products[{{$key}}][variation_id]" class="row_variation_id">

		<input type="hidden" value="{{ $orderDetail->product->product->enable_stock }}" name="products[{{$key}}][enable_stock]">

							
		<div class="input-group input-number">
			<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
		<input type="text" data-min="1" class="form-control pos_quantity input_number mousetrap" value="{{$orderDetail->quantity}}" name="products[{{$key}}][quantity]" data-decimal="0" data-rule-abs_digit="true" data-msg-abs_digit="Decimal value not allowed" data-rule-required="true" data-msg-required="This field is required" data-rule-max-value="45.0000" data-qty_available="45.0000" data-msg-max-value="Only 45.00 Pcs available" data-msg_max_default="Only 45.00 Pcs available">
		<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
		</div>
		Pcs

	</td>
	<td class="hide">
		<input type="text" name="products[{{$key}}][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number" value="{{ $order->orderDetails->sum('tax') }}">
	</td>
	<td class="text-center v-center">
				<input type="text" class="form-control pos_line_total  input_number " value="{{ $orderDetail->price }}">
		<span class="display_currency pos_line_total_text  hide " data-currency_symbol="true">৳ {{ $orderDetail->price }} </span>
	</td>
    
	<td class="text-center">
		<i class="fa fa-close text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
	</td>
</tr>
@endforeach
	
{{-- AAAAAAAAAAAAAAAAAAAAA --}}



</tbody>
						</table>
						</div>
						@php
						$order->discount = 0;
						   $order->netTotal =   $order->grand_total;
			   
			   
						   if(!empty($order->discount_type)) {
			   
							   if(empty($order->discount_amount)) {
								   $order->discount_amount = 0;
							   }
							   if($order->discount_type == "fixed") {
								   $order->discount =  $order->discount_amount;
							   } else {
								   $order->discount = $order->grand_total  * ($order->discount_amount/100);
							   }
			   
						   }
			   @endphp
						<div class="table-responsive">
							<table class="table table-condensed table-bordered table-striped">
								<tbody><tr>
									<td>
										<div class="pull-right"><b>Total: </b>
										
											<span class="price_total"> {{ $order->netTotal + $order->shipping + $order->area_shipping - $order->discount }}</span>
										</div>
									</td>
								</tr>
							</tbody></table>
							</div>
					</div>
				</div>
			</div><!-- /.box -->
			<div class="box box-solid">
				<div class="box-body">
					<div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('discount_type', __('sale.discount_type') . ':*' ) !!}
				            <div class="input-group">
				                <span class="input-group-addon">
				                    <i class="fa fa-info"></i>
				                </span>
				                {!! Form::select('discount_type', [
									'fixed' => __('lang_v1.fixed'),
									 'percentage' => __('lang_v1.percentage')], 'fixed' , ['class' => 'form-control','placeholder' => __('messages.please_select'), 'required', 'data-default' => 'fixed']); !!}
				            </div>
				        </div>
				    </div>
				    <div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('discount_amount', __('sale.discount_amount') . ':*' ) !!}
				            <div class="input-group">
				                <span class="input-group-addon">
				                    <i class="fa fa-info"></i>
				                </span>
				                {!! Form::text('discount_amount', @num_format( $order->discount ), ['class' => 'form-control input_number', 'data-default' => $order->discount ]); !!}
				            </div>
				        </div>
				    </div>
				    <div class="col-md-4"><br>
				    	<b>@lang( 'sale.discount_amount' ):</b>(-) 
						<span class="display_currency" id="total_discount">{{$order->discount}}</span>
				    </div>
				    <div class="clearfix"></div>
				    <div class="col-md-4">
				    	<div class="form-group">
				            {!! Form::label('tax_rate_id', __('sale.order_tax') . ':*' ) !!}
				            <div class="input-group">
				                <span class="input-group-addon">
				                    <i class="fa fa-info"></i>
				                </span>
				                {!! Form::select('tax_rate_id', $taxes['tax_rates'], $business_details->default_sales_tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control', 'data-default'=> $business_details->default_sales_tax], $taxes['attributes']); !!}

								<input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" 
								value="@if(empty($edit)) {{@num_format($business_details->tax_calculation_amount)}} @else {{@num_format(optional($transaction->tax)->amount)}} @endif" data-default="{{$business_details->tax_calculation_amount}}">
				            </div>
				        </div>
				    </div>
				    <div class="col-md-4 col-md-offset-4">
				    	<b>@lang( 'sale.order_tax' ):</b>(+) 
						<span class="display_currency" id="order_tax">0</span>
				    </div>
				    <div class="clearfix"></div>
					<div class="col-md-4">
						<div class="form-group">
				            {!! Form::label('shipping_details', __('sale.shipping_details')) !!}
				            <div class="input-group">
								<span class="input-group-addon">
				                    <i class="fa fa-info"></i>
				                </span>
				                {!! Form::textarea('shipping_details',null, ['class' => 'form-control','placeholder' => __('sale.shipping_details') ,'rows' => '1', 'cols'=>'30']); !!}
				            </div>
				        </div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							{!!Form::label('shipping_charges', __('sale.shipping_charges'))!!}
							<div class="input-group">
							<span class="input-group-addon">
							<i class="fa fa-info"></i>
							</span>
							{!!Form::text('shipping_charges',@num_format($order->shipping),['class'=>'form-control input_number','placeholder'=> __('sale.shipping_charges')]);!!}
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
				    <div class="col-md-4 col-md-offset-8">
				    	<div><b>@lang('sale.total_payable'): </b>
							<input type="hidden" name="final_total" id="final_total_input" value="{{ $order->netTotal + $order->shipping + $order->area_shipping - $order->discount }}">
							<span id="total_payable">0</span>
						</div>
				    </div>
				    <div class="col-md-12">
				    	<div class="form-group">
							{!! Form::label('sell_note',__('sale.sell_note')) !!}
							{!! Form::textarea('sale_note', null, ['class' => 'form-control', 'rows' => 3]); !!}
						</div>
				    </div>
				    <input type="hidden" name="is_direct_sale" value="0">
					<input type="hidden" name="is_ecommerce_sale" value="1">
				</div>
			</div><!-- /.box -->

		</div>
	</div>
	<!--box end-->
	<div class="box box-solid" id="payment_rows_div"><!--box start-->
		<div class="box-header">
			<h3 class="box-title">
				@lang('purchase.add_payment')
			</h3>
		</div>
		<div class="box-body payment_row">
		
			<div class="row">
				<input type="hidden" class="payment_row_index" value="{{0}}">
				@php
					$col_class = 'col-md-6';
					if(!empty($accounts)){
						$col_class = 'col-md-4';
					}
				@endphp
				<div class="{{$col_class}}">
					<div class="form-group">
						{!! Form::label("amount_0" ,__('sale.amount') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-money"></i>
							</span>
							
							{!! Form::text("payment[0][amount]", @num_format(	 $order->netTotal + $order->shipping + $order->area_shipping - $order->discount ), ['class' => 'form-control payment-amount input_number', 'required', 'id' => "amount_0", 'placeholder' => __('sale.amount')]); !!}
						</div>
					</div>
				</div>
				<div class="{{$col_class}}">
					<div class="form-group">
						{!! Form::label("method_0" , __('lang_v1.payment_method') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-money"></i>
							</span>
							{!! Form::select("payment[0][method]", $payment_types, $payment_line['method'], ['class' => 'form-control col-md-12 payment_types_dropdown', 'required', 'id' => "method_0", 'style' => 'width:100%;']); !!}
						</div>
					</div>
				</div>
				@if(!empty($accounts))
					<div class="{{$col_class}}">
						<div class="form-group">
							{!! Form::label("account_0" , __('lang_v1.payment_account') . ':') !!}
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fa fa-money"></i>
								</span>
								{!! Form::select("payment[0][account_id]", $accounts, !empty($payment_line['account_id']) ? $payment_line['account_id'] : '' , ['class' => 'form-control select2', 'id' => "account_0", 'style' => 'width:100%;']); !!}
							</div>
						</div>
					</div>
				@endif
				<div class="clearfix"></div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("card_transaction_number_0",__('lang_v1.card_transaction_no')) !!}
                        {!! Form::text("payment[0][card_transaction_number]", "nothing", ['class' => 'form-control','required', 'placeholder' => __('lang_v1.card_transaction_no'), 'id' => "card_transaction_number_0"]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group" style="font-size:12px">
                        {!! Form::label("card_number_0", __('Bank Name')) !!}
                        <select class="form-control" name="bank_name" style="font-size:12px">
                        <option selected value="pk" >Other Bank(2%)</option>
                        <option value="sk">City bank(3%)</option>
                        <select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("card_number_0", __('lang_v1.card_no')) !!}
                        {!! Form::text("payment[0][card_number]", "nothing", ['class' => 'form-control', 'required','placeholder' => __('lang_v1.card_no'), 'id' => "card_number_0"]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("card_type_0", __('lang_v1.card_type')) !!}
                        {!! Form::select("payment[0][card_type]", ['credit' => 'Credit Card', 'debit' => 'Debit Card','visa' => 'Visa', 'master' => 'MasterCard'], "credit",['class' => 'form-control', 'id' => "card_type_0" ]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("card_holder_name_0", __('lang_v1.card_holder_name')) !!}
                        {!! Form::text("payment[0][card_holder_name]", "nothing", ['class' => 'form-control', 'placeholder' => __('lang_v1.card_holder_name'), 'id' => "card_holder_name_0"]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("card_month_0", __('lang_v1.month')) !!}
                        {!! Form::text("payment[0][card_month]", "nothing", ['class' => 'form-control', 'placeholder' => __('lang_v1.month'),
                        'id' => "card_month_0" ]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("card_security_0",__('lang_v1.security_code')) !!}
                        {!! Form::text("payment[0][card_security]", "nothing", ['class' => 'form-control', 'placeholder' => __('lang_v1.security_code'), 'id' => "card_security_0"]); !!}
                    </div>
                </div>
                <div class="payment_details_div" data-type="cheque" >
					<div class="col-md-12">
						<div class="form-group">
							{!! Form::label("cheque_number_0",__('lang_v1.cheque_no')) !!}
							{!! Form::text("payment[0][cheque_number]", "nothing", ['class' => 'form-control','required', 'placeholder' => __('lang_v1.cheque_no'), 'id' => "cheque_number_0"]); !!}
						</div>
					</div>
				</div>
                <div class="payment_details_div @if( $payment_line['method'] !== 'bank_transfer' ) {{ 'hide' }} @endif" data-type="bank_transfer" >
					<div class="col-md-12">
						<div class="form-group">
							{!! Form::label("bank_account_number_0",__('lang_v1.bank_account_number')) !!}
							{!! Form::text( "payment[0][bank_account_number]", "nothing", ['class' => 'form-control','required', 'placeholder' => __('lang_v1.bank_account_number'), 'id' => "bank_account_number_0"]); !!}
						</div>
					</div>
				</div>
                <div class="col-md-12">
					<div class="form-group">
						{!! Form::label("note_0", __('sale.payment_note') . ':') !!}
						{!! Form::textarea("payment[0][note]", "", ['class' => 'form-control', 'rows' => 3, 'id' => "note_0"]); !!}
					</div>
				</div> 
				{{-- <div class="payment_details_div @if( $payment_line['method'] !== 'card' ) {{ 'hide' }} @endif" data-type="card" >
					<div class="col-md-3">
						<div class="form-group" style="font-size:12px">
							{!! Form::label("card_number_0", __('Bank Name')) !!}
							<select class="form-control" name="bank_name" style="font-size:12px">
							<option value="pk" >Other Bank(2%)</option>
							<option value="sk">City bank(3%)</option>
							<select>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							{!! Form::label("card_type_0", __('lang_v1.card_type')) !!}
							{!! Form::select("payment[0][card_type]", ['credit' => 'Credit Card', 'debit' => 'Debit Card','visa' => 'Visa', 'master' => 'MasterCard'], $payment_line['card_type'],['class' => 'form-control', 'id' => "card_type_0" ]); !!}
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							{!! Form::label("card_number_0", __('lang_v1.card_no')) !!}
							{!! Form::text("payment[0][card_number]", $payment_line['card_number'], ['class' => 'form-control', 'required','placeholder' => __('lang_v1.card_no'), 'id' => "card_number_0"]); !!}
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							{!! Form::label("card_holder_name_0", __('lang_v1.card_holder_name')) !!}
							{!! Form::text("payment[0][card_holder_name]", $payment_line['card_holder_name'], ['class' => 'form-control', 'placeholder' => __('lang_v1.card_holder_name'), 'id' => "card_holder_name_0"]); !!}
						</div>
					</div>
					
					<div class="clearfix"></div>
					<div class="col-md-3">
						<div class="form-group">
							{!! Form::label("card_transaction_number_0",__('lang_v1.card_transaction_no')) !!}
							{!! Form::text("payment[0][card_transaction_number]", $payment_line['card_transaction_number'], ['class' => 'form-control','required', 'placeholder' => __('lang_v1.card_transaction_no'), 'id' => "card_transaction_number_0"]); !!}
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							{!! Form::label("card_month_0", __('lang_v1.month')) !!}
							{!! Form::text("payment[0][card_month]", $payment_line['card_month'], ['class' => 'form-control', 'placeholder' => __('lang_v1.month'),
							'id' => "card_month_0" ]); !!}
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							{!! Form::label("card_year_0", __('lang_v1.year')) !!}
							{!! Form::text("payment[0][card_year]", $payment_line['card_year'], ['class' => 'form-control', 'placeholder' => __('lang_v1.year'), 'id' => "card_year_0" ]); !!}
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							{!! Form::label("card_security_0",__('lang_v1.security_code')) !!}
							{!! Form::text("payment[0][card_security]", $payment_line['card_security'], ['class' => 'form-control', 'placeholder' => __('lang_v1.security_code'), 'id' => "card_security_0"]); !!}
						</div>
					</div>
					<div class="clearfix"></div>
				</div> --}}
				{{-- <div class="payment_details_div @if( $payment_line['method'] !== 'cheque' ) {{ 'hide' }} @endif" data-type="cheque" >
					<div class="col-md-12">
						<div class="form-group">
							{!! Form::label("cheque_number_0",__('lang_v1.cheque_no')) !!}
							{!! Form::text("payment[0][cheque_number]", $payment_line['cheque_number'], ['class' => 'form-control','required', 'placeholder' => __('lang_v1.cheque_no'), 'id' => "cheque_number_0"]); !!}
						</div>
					</div>
				</div> --}}
				{{-- <div class="payment_details_div @if( $payment_line['method'] !== 'bank_transfer' ) {{ 'hide' }} @endif" data-type="bank_transfer" >
					<div class="col-md-12">
						<div class="form-group">
							{!! Form::label("bank_account_number_0",__('lang_v1.bank_account_number')) !!}
							{!! Form::text( "payment[0][bank_account_number]", $payment_line['bank_account_number'], ['class' => 'form-control','required', 'placeholder' => __('lang_v1.bank_account_number'), 'id' => "bank_account_number_0"]); !!}
						</div>
					</div>
				</div> --}}
				{{-- <div class="payment_details_div @if( $payment_line['method'] !== 'custom_pay_1' ) {{ 'hide' }} @endif" data-type="custom_pay_1" >
					<div class="col-md-12">
						<div class="form-group">
							{!! Form::label("transaction_no_1_0", __('lang_v1.transaction_no')) !!}
							{!! Form::text("payment[0][transaction_no_1]", $payment_line['transaction_no'], ['class' => 'form-control','required', 'placeholder' => __('lang_v1.transaction_no'), 'id' => "transaction_no_1_0"]); !!}
						</div>
					</div>
				</div> --}}
				{{-- <div class="payment_details_div @if( $payment_line['method'] !== 'custom_pay_2' ) {{ 'hide' }} @endif" data-type="custom_pay_2" >
					<div class="col-md-12">
						<div class="form-group">
							{!! Form::label("transaction_no_2_0", __('lang_v1.transaction_no')) !!}
							{!! Form::text("payment[0][transaction_no_2]", $payment_line['transaction_no'], ['class' => 'form-control','required', 'placeholder' => __('lang_v1.transaction_no'), 'id' => "transaction_no_2_0"]); !!}
						</div>
					</div>
				</div> --}}
				{{-- <div class="payment_details_div @if( $payment_line['method'] !== 'custom_pay_3' ) {{ 'hide' }} @endif" data-type="custom_pay_3" >
					<div class="col-md-12">
						<div class="form-group">
							{!! Form::label("transaction_no_3_0", __('lang_v1.transaction_no')) !!}
							{!! Form::text("payment[0][transaction_no_3]", $payment_line['transaction_no'], ['class' => 'form-control','required', 'placeholder' => __('lang_v1.transaction_no'), 'id' => "transaction_no_3_0"]); !!}
						</div>
					</div>
				</div> --}}
				{{-- <div class="col-md-12">
					<div class="form-group">
						{!! Form::label("note_0", __('sale.payment_note') . ':') !!}
						{!! Form::textarea("payment[0][note]", $payment_line['note'], ['class' => 'form-control', 'rows' => 3, 'id' => "note_0"]); !!}
					</div>
				</div> --}}
			</div>
			<hr>
			<div class="row">
				<div class="col-sm-12">
					<div class="pull-right"><strong>@lang('lang_v1.balance'):</strong> <span class="balance_due">0.00</span></div>
				</div>
			</div>
			<br>
			
		</div>
	</div>

</section>
<div class="row text-center">
 <h2>Add this order to pos sell</h2>
 <button type="submit"  class="btn btn-primary">Confirm</button>

</div>
{!! Form::close() !!}
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

@stop

@section('javascript')
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
@endsection
