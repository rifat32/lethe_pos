<div class="row">
	<div class="col-sm-12">
		<div  class="panel panel-default">
			<div  class="panel-body bg-gray disabled" style="margin-bottom: 0px !important">
				<table  class="table table-condensed" 
					style="margin-bottom: 0px !important; background:#dae6e4;">
					<tbody>
					<tr>
						<td>
							<div class="col-sm-1 col-xs-3 d-inline-table">
								<b>@lang('sale.item'):</b> 
								<br/>
								<span class="total_quantity">0</span>
							</div>

							<div class="col-sm-2 col-xs-3 d-inline-table">
								<b>@lang('sale.total'):</b> 
								<br/>
								<span class="price_total">0</span>
							</div>

							<div class="col-sm-2 col-xs-6 d-inline-table">

								<span class="@if($pos_settings['disable_discount'] != 0) hide @endif">

								<b>@lang('sale.discount')(-): @show_tooltip(__('tooltip.sale_discount'))</b> 
								<br/>
								<i class="fa fa-pencil-square-o cursor-pointer" id="pos-edit-discount" title="@lang('sale.edit_discount')" aria-hidden="true" data-toggle="modal" data-target="#posEditDiscountModal"></i>
								<span id="total_discount">0</span>
								<input type="hidden" name="discount_type" id="discount_type" value="@if(empty($edit)){{'percentage'}}@else{{$transaction->discount_type}}@endif" data-default="percentage">

								<input type="hidden" name="discount_amount" id="discount_amount" value="@if(empty($edit)) {{@num_format($business_details->default_sales_discount)}} @else {{@num_format($transaction->discount_amount)}} @endif" data-default="{{$business_details->default_sales_discount}}">

								</span>
							</div>

							<div class="col-sm-2 col-xs-6 d-inline-table">

								<span class="@if($pos_settings['disable_order_tax'] != 0) hide @endif">

								<b>@lang('sale.order_tax')(+): @show_tooltip(__('tooltip.sale_tax'))</b>
								<br/>
								<i class="fa fa-pencil-square-o cursor-pointer" title="@lang('sale.edit_order_tax')" aria-hidden="true" data-toggle="modal" data-target="#posEditOrderTaxModal" id="pos-edit-tax" ></i> 
								<span id="order_tax">
									@if(empty($edit))
										0
									@else
										{{$transaction->tax_amount}}
									@endif
								</span>

								<input type="hidden" name="tax_rate_id" 
									id="tax_rate_id" 
									value="@if(empty($edit)) {{$business_details->default_sales_tax}} @else {{$transaction->tax_id}} @endif" 
									data-default="{{$business_details->default_sales_tax}}">

								<input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" 
									value="@if(empty($edit)) {{@num_format($business_details->tax_calculation_amount)}} @else {{@num_format(optional($transaction->tax)->amount)}} @endif" data-default="{{$business_details->tax_calculation_amount}}">

								</span>
							</div>
							
							<!-- shipping -->
							<div class="col-sm-2 col-xs-6 d-inline-table">

								<span class="@if($pos_settings['disable_discount'] != 0) hide @endif">

								<b>@lang('sale.shipping')(+): @show_tooltip(__('tooltip.shipping'))</b> 
								<br/>
								<i class="fa fa-pencil-square-o cursor-pointer"  title="@lang('sale.shipping')" aria-hidden="true" data-toggle="modal" data-target="#posShippingModal"></i>
								<span id="shipping_charges_amount">0</span>
								<input type="hidden" name="shipping_details" id="shipping_details" value="@if(empty($edit)){{""}}@else{{$transaction->shipping_details}}@endif" data-default="">

								<input type="hidden" name="shipping_charges" id="shipping_charges" value="@if(empty($edit)){{@num_format(0.00)}} @else{{@num_format($transaction->shipping_charges)}} @endif" data-default="0.00">

								</span>
							</div>

							
							<div class="col-sm-3 col-xs-12 d-inline-table">
								<b>@lang('sale.total_payable'):</b>
								<br/>
								<input type="hidden" name="final_total" 
									id="final_total_input" value=0>
								<span id="total_payable" class="text-success lead text-bold">0</span>
								@if(empty($edit))
									<button type="button" class="btn btn-danger btn-flat btn-xs pull-right" id="pos-cancel">@lang('sale.cancel')</button>
								@else
									<button type="button" class="btn btn-danger btn-flat hide btn-xs pull-right" id="pos-delete">@lang('messages.delete')</button>
								@endif
							</div>
						</td>
					</tr>

					<tr>
						<td >
							<div class="col-sm-4 col-xs-12 col-2px-padding" style="display:none;"  >

								<button 
								type="button" 
									class="btn btn-warning btn-block btn-flat @if($pos_settings['disable_draft'] != 0) hide @endif" 
									id="pos-draft"
									>@lang('sale.draft')</button>

								<button type="button" 
								class="btn   btn-block btn-flat btn-lg no-print @if($pos_settings['disable_pay_checkout'] != 0) hide @endif pos-express-btn" 
								style="margin-top:-0.1rem; background:#a35c0a;!important"
									id="pos-quotation" >
									<b style="color:aliceblue;"> 	@lang('lang_v1.quotation')</b>
								</button>
							</div>
							<div class="col-sm-3 col-xs-6 col-2px-padding" style="display: none;">
								<button type="button" 
								class="btn bg-maroon btn-block btn-flat no-print @if(!empty($pos_settings['disable_suspend'])) pos-express-btn btn-lg @endif pos-express-finalize" 
								data-pay_method="card"
								title="@lang('lang_v1.tooltip_express_checkout_card')" >
								<div class="text-center">
									<i class="fa fa-check" aria-hidden="true"></i>
    								<b>@lang('lang_v1.express_checkout_card')</b>
    							</div>
								</button>
								@if(empty($pos_settings['disable_suspend']))
									<button type="button" 
									class="btn bg-red btn-block btn-flat no-print pos-express-finalize" 
									data-pay_method="suspend"
									title="@lang('lang_v1.tooltip_suspend')" >
									<div class="text-center">
										<i class="fa fa-pause" aria-hidden="true"></i>
	    								<b>@lang('lang_v1.suspend')</b>
	    							</div>
									</button>
								@endif
							</div>
							<div style="margin-left: 25rem;" class="col-sm-4 col-xs-12 col-2px-padding">
								<button 
								style=" background:#68a30a;!important"
								type="button" class="btn  btn-block btn-flat btn-lg no-print @if($pos_settings['disable_pay_checkout'] != 0) hide @endif pos-express-btn" id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')">
								<div class="text-center">
									<i class="fa fa-check" aria-hidden="true"></i>
    								<b style="color:aliceblue;">@lang('lang_v1.checkout_multi_pay')</b>
    							</div>
								</button>
							</div>
							<div class="col-sm-3 col-xs-12 col-2px-padding">
								<button type="button" class="btn bg-navy btn-block btn-flat btn-lg no-print @if($pos_settings['disable_express_checkout'] != 0) hide @endif pos-express-btn pos-express-finalize"
								data-pay_method="cash"
								title="@lang('tooltip.express_checkout')">
								<div class="text-center">
									<i class="fa fa-check" aria-hidden="true"></i>
    								<b>@lang('lang_v1.express_checkout_cash')</b>
    							</div>
								</button>
							</div>

							<div class="div-overlay pos-processing"></div>
						</td>
					</tr>

					</tbody>
				</table>

				<!-- Button to perform various actions -->
				<div class="row">
					
				</div>
			</div>
		</div>
	</div>
</div>

