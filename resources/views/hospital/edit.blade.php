@extends('layouts.app')
@section('title', __('product.edit_product'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Edit Service</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action('ServiceController@update' , [$product->id] ), 'method' => 'PUT', 'id' => 'product_add_form',
        'class' => 'product_form', 'files' => true ]) !!}
  <input type="hidden" id="product_id" value="{{ $product->id }}">
	<div class="box box-solid">
    <div class="box-body">
      <div class="row">
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('name', "Name" . ':*') !!}
              {!! Form::text('name', $product->name, ['class' => 'form-control', 'required',
              'placeholder' => __('product.product_name')]); !!}
          </div>
        </div>

        <div class="col-sm-4 hide @if(!session('business.enable_brand')) hide @endif">
          <div class="form-group">
            {!! Form::label('brand_id', __('product.brand') . ':') !!}
            <div class="input-group">
              {!! Form::select('brand_id', $brands, $product->brand_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
              <span class="input-group-btn">
                <button type="button" @if(!auth()->user()->can('brand.create')) disabled @endif class="btn btn-default bg-white btn-flat btn-modal" data-href="{{action('BrandController@create', ['quick_add' => true])}}" title="@lang('brand.add_brand')" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
              </span>
            </div>
          </div>
        </div>

        <div class="col-sm-4 hide">
          <div class="form-group">
            {!! Form::label('unit_id', __('product.unit') . ':*') !!}
            <div class="input-group">
              {!! Form::select('unit_id', $units, $product->unit_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
              <span class="input-group-btn">
                <button type="button" @if(!auth()->user()->can('unit.create')) disabled @endif class="btn btn-default bg-white btn-flat quick_add_unit btn-modal" data-href="{{action('UnitController@create', ['quick_add' => true])}}" title="@lang('unit.add_unit')" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
              </span>
            </div>
          </div>
        </div>

        <div class="clearfix"></div>
        <input type="hidden" name="category_id" value="30"/>
      

       

        <div class="col-sm-4 hide @if(!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
          <div class="form-group">
            {!! Form::label('sku', __('product.sku')  . ':*') !!} @show_tooltip(__('tooltip.sku'))
            {!! Form::text('sku', $product->sku, ['class' => 'form-control',
            'placeholder' => __('product.sku'), 'required']); !!}
          </div>
        </div>
           {{-- <div >

          <div lass="col-sm-6">
                {!! Form::label('name', "Shipping Price". ':*') !!}
                  {!! Form::text('shipping_price', $product->shipping_price, ['class' => 'form-control', 'required', 'placeholder' =>"Shipping Price"]); !!}
              </div>

            </div> --}}

        <div class="clearfix"></div>
        <div class="c           ol-sm-4" style="display:none">
          <div class="form-group">
            {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
              {!! Form::select('barcode_type', $barcode_types, $product->barcode_type, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); !!}
          </div>
        </div>
        <div class="col-sm-4 hide">
          <div class="form-group">
          <br>
            <label>
              {!! Form::checkbox('enable_stock', 1, $product->enable_stock, ['class' => 'input-icheck', 'id' => 'enable_stock']); !!} <strong>@lang('product.manage_stock')</strong>
            </label>@show_tooltip(__('tooltip.enable_stock')) <p class="help-block"><i>@lang('product.enable_stock_help')</i></p>
          </div>
        </div>
        <div class="col-sm-4 hide" id="alert_quantity_div" @if(!$product->enable_stock) style="display:none" @endif>
          <div class="form-group">
            {!! Form::label('alert_quantity', __('product.alert_quantity') . ':*') !!} @show_tooltip(__('tooltip.alert_quantity'))
            {!! Form::number('alert_quantity', $product->alert_quantity, ['class' => 'form-control', 'required',
            'placeholder' => __('product.alert_quantity') , 'min' => '0']); !!}
          </div>
        </div>
        <div class="col-sm-4 hide">
            {!! Form::label('name', "Discount". ':*') !!}
              {!! Form::text('discount', $product->discount, ['class' => 'form-control', 'required', 'placeholder' =>"Discount"]); !!}
          </div>
          <div class="col-sm-4 hide">
            <div class="form-group">
                {!! Form::label('name', "Doctor Commission". ':*') !!}
                  {!! Form::text('doctor_commission', $product->doctor_commission, ['class' => 'form-control', 'required', 'placeholder' =>"Commission"]); !!}
              </div>
          </div>

        </div>
 <div >


        <!--<div class="clearfix"></div>-->
        <div class="col-sm-12 hide" >
                  <div class="form-group">
            {!! Form::label('product_description', __('lang_v1.product_description') . ':') !!}
              {!! Form::textarea('product_description', $product->product_description, ['class' => 'form-control']); !!}
          </div>
        </div>
        <div class="col-sm-4 hide">
          <div class="form-group">
            {!! Form::label('image', __('lang_v1.product_image') . ':') !!}
            {!! Form::file('image', ['id' => 'upload_image', 'accept' => 'image/*']); !!}
            <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]). @lang('lang_v1.aspect_ratio_should_be_1_1') @if(!empty($product->image)) <br> @lang('lang_v1.previous_image_will_be_replaced') @endif</p></small>
          </div>
        </div>

      </div>
    </div>
  </div>
  <div class="box box-solid" style="display:none">
    <div class="box-body">
      <div class="row">
        @if(session('business.enable_product_expiry'))

          @if(session('business.expiry_type') == 'add_expiry')
            @php
              $expiry_period = 12;
              $hide = true;
            @endphp
          @else
            @php
              $expiry_period = null;
              $hide = false;
            @endphp
          @endif
          <div class="col-sm-4 @if($hide) hide @endif">
            <div class="form-group">
              <div class="multi-input">
                @php
                  $disabled = false;
                  $disabled_period = false;
                  if( empty($product->expiry_period_type) || empty($product->enable_stock) ){
                    $disabled = true;
                  }
                  if( empty($product->enable_stock) ){
                    $disabled_period = true;
                  }
                @endphp
                  {!! Form::label('expiry_period', __('product.expires_in') . ':') !!}<br>
                  {!! Form::text('expiry_period', @num_format($product->expiry_period), ['class' => 'form-control pull-left input_number',
                    'placeholder' => __('product.expiry_period'), 'style' => 'width:60%;', 'disabled' => $disabled]); !!}
                  {!! Form::select('expiry_period_type', ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ], $product->expiry_period_type, ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type', 'disabled' => $disabled_period]); !!}
              </div>
            </div>
          </div>
          @endif
          <div class="col-sm-4">
            <div class="checkbox">
              <label>
                {!! Form::checkbox('enable_sr_no', 1, $product->enable_sr_no, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.enable_imei_or_sr_no')</strong> @show_tooltip(__('lang_v1.tooltip_sr_no'))
              </label>
            </div>
          </div>

        <!-- Rack, Row & position number -->
        @if(session('business.enable_racks') || session('business.enable_row') || session('business.enable_position'))
          <div class="col-md-12">
            <h4>@lang('lang_v1.rack_details'):
              @show_tooltip(__('lang_v1.tooltip_rack_details'))
            </h4>
          </div>
          @foreach($business_locations as $id => $location)
            <div class="col-sm-3">
              <div class="form-group">
                {!! Form::label('rack_' . $id,  $location . ':') !!}


                  @if(!empty($rack_details[$id]))
                    @if(session('business.enable_racks'))
                      {!! Form::text('product_racks_update[' . $id .                 '][rack]', $rack_details[$id]['rack'], ['class' => 'form-control', 'id' => 'rack_' . $id]); !!}
                    @endif

                    @if(session('business.enable_row'))
                      {!! Form::text('product_racks_update[' . $id . '][row]', $rack_details[$id]['row'], ['class' => 'form-control']); !!}
                    @endif

                    @if(session('business.enable_position'))
                      {!! Form::text('product_racks_update[' . $id . '][position]', $rack_details[$id]['position'], ['class' => 'form-control']); !!}
                    @endif
                  @else
                    {!! Form::text('product_racks[' . $id . '][rack]', null, ['class' => 'form-control', 'id' => 'rack_' . $id, 'placeholder' => __('lang_v1.rack')]); !!}

                    {!! Form::text('product_racks[' . $id . '][row]', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.row')]); !!}

                    {!! Form::text('product_racks[' . $id . '][position]', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.position')]); !!}
                  @endif

              </div>
            </div>
          @endforeach
        @endif


        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('weight',  __('lang_v1.weight') . ':') !!}
            {!! Form::text('weight', $product->weight, ['class'         =>         'form-control', 'placeholder' => __('lang_v1.weight')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <!--custom fields-->
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field1',  __('lang_v1.product_custom_field1') . ':') !!}
            {!! Form::text('product_custom_field1', $product->product_custom_field1, ['class' => 'form-control', 'placeholder' => __('lang_v1.product_custom_field1')]); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field2',  __('lang_v1.product_custom_field2') . ':') !!}
            {!! Form::text('product_custom_field2', $product->product_custom_field2, ['class' => 'form-control', 'placeholder' => __('lang_v1.product_custom_field2')]); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field3',  __('lang_v1.product_custom_field3') . ':') !!}
            {!! Form::text('product_custom_field3', $product->product_custom_field3, ['class' => 'form-control', 'placeholder' => __('lang_v1.product_custom_field3')]); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field4',  __('lang_v1.product_custom_field4') . ':') !!}
            {!! Form::text('product_custom_field4', $product->product_custom_field4, ['class' => 'form-control', 'placeholder' => __('lang_v1.product_custom_field4')]); !!}
          </div>
        </div>
        <!--custom fields-->

      </div>
    </div>
  </div>

  <div class="box box-solid">
    <div class="box-body">
      <div class="row">
		<div class="col-sm-4 hide">
          <div class="form-group">
            {!! Form::label('type', __('product.product_type') .         ':*') !!} @show_tooltip(__('tooltip.product_type'))
            {!! Form::select('type', ['single' => 'Single', 'variable' => 'Variable'], $product->type, ['class' => 'form-control select2',
              'required','disabled', 'data-action' => 'edit', 'data-product_id' => $product->id ]); !!}
          </div>
        </div>
        <div class="col-sm-4  hide @if(!session('business.enable_price_tax')) hide @endif">
          <div class="form-group">
            {!! Form::label('tax', __('product.applicable_tax') . ':') !!}
              {!! Form::select('tax', $taxes, $product->tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'], $tax_attributes); !!}
          </div>
        </div>

        <div class="col-sm-4 hide @if(!session('business.enable_price_tax')) hide @endif">
          <div class="form-group">
            {!! Form::label('tax_type', __('product.selling_price_tax_type') . ':*') !!}
              {!! Form::select('tax_type',['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')], $product->tax_type,
              ['class' => 'form-control select2', 'required']); !!}
          </div>

        </div>

        </div>
        <div class="clearfix"></div>


        @php
        $product_deatails = \App\ProductVariation::where('product_id', $product->id)
                                                    ->with(['variations'])
                                                    ->orderby('id','desc')
                                                    ->first();
        $default = null;
        $class = '';
        @endphp
        
        
        <div class="col-sm-12"><br>
            <div class="table-responsive">
            <table class="table table-bordered add-product-price-table table-condensed {{$class}}">
                <tr>
            <th>Service Value</th>
          <th>Cost</th>
                </tr>
                @foreach($product_deatails->variations as $variation )
                    @if($loop->first)
                        <tr>
                            <td>
                                <input type="hidden" name="single_variation_id" value="{{$variation->id}}">
        
                                <div class="col-sm-6">
                                  {!! Form::label('single_dpp', trans('product.exc_of_tax') . ':*') !!}
        
                                  {!! Form::text('single_dpp', @num_format($variation->default_purchase_price), ['class' => 'form-control input-sm dpp input_number', 'placeholder' => 'Excluding Tax', 'required']); !!}
                                </div>
        
                                <div class=" hide" >
                                  {!! Form::label('single_dpp_inc_tax', trans('product.inc_of_tax') . ':*') !!}
                                
                                  {!! Form::text('single_dpp_inc_tax', @num_format($variation->dpp_inc_tax), ['class' => 'form-control input-sm dpp_inc_tax input_number', 'placeholder' => 'Including Tax', 'required']); !!}
                                </div>
                            </td>
                            <td>
                              <div  >
                                {!! Form::label('single_dpp_inc_tax', "cost" . ':*') !!}
                              
                                {!! Form::text('cost', @num_format($variation->cost), ['class' => 'form-control input-sm dpp_inc_tax input_number', 'placeholder' => 'Cost', 'required']); !!}
                              </div>
                            </td>
        
                              <input type="hidden" name="sd_single_dsp" id="sd_single_dsp" value="{{@num_format($variation->sd_single_dsp)}}">
                              <input type="hidden" name="sd_single_dsp_inc_tax" id="sd_single_dsp_inc_tax" value="{{@num_format($variation->sd_single_dsp_inc_tax)}}">
                            <td class="hide">
                                <br/>
                                {!! Form::text('profit_percent', @num_format($variation->profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent', 'required',"type"=> "hidden"]); !!}
                            </td>
        
                            <td class="hide">
                                <label><span class="dsp_label"></span></label>
                                {!! Form::text('single_dsp', @num_format($variation->default_sell_price), ['class' => 'form-control input-sm dsp input_number', 'placeholder' => 'Excluding tax', 'id' => 'single_dsp', 'required']); !!}
        
                                {!! Form::text('single_dsp_inc_tax', @num_format($variation->sell_price_inc_tax), ['class' => 'form-control input-sm hide input_number', 'placeholder' => 'Including tax', 'id' => 'single_dsp_inc_tax', 'required']); !!}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </table>
            </div>
        </div>
        {{-- <div class="form-group col-sm-11 col-sm-offset-1" id="product_form_part"></div> --}}






        <input type="hidden" id="variation_counter" value="0">
        <input type="hidden" id="default_profit       _percent" value="{        { $default_profit_percent }}">

		<div class="col-sm-4 f        orm-group" style="margin-left: 10%; display:none">
          <label style="background: #5cb85c;color: #fff;padding: 4px;"> Wholesale Price:*</label>
          <input type="text" value="{{$product->reseller_price}}"name="reseller_price" class="form-contro		l" >
         </div>
         <div class="col-sm-4 form-group" style="display:none">
          <label style="background: #5cb85c;color: #fff;padding: 4px;">Regular Price:*</label>
          <input type="text" value="{{$product->mrp_price}}"name="mrp_price" class="form-control">
         </div>

      </div>
    </div>
  </div>
  
  <div class="row">
    
    <input type="hidden" name="submit_type" id="submit_type">
        <div class="col-sm-12">
          <div class="text-center">
            <div class="btn-group">
              {{-- @if($selling_price_group_count)
                <button type="submit" value="submit_n_add_selling_prices" class="btn btn-warning submit_product_form">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
              @endif --}}

              {{-- <button type="submit" @if(empty($product->enable_stock)) disabled="true" @endif id="opening_stock_button"  value="update_n_edit_opening_stock" class="btn bg-purple submit_product_form">@lang('lang_v1.update_n_edit_opening_stock')</button> --}}

              {{-- <button type="submit" value="save_n_add_another" class="btn bg-maroon submit_product_form">@lang('lang_v1.update_n_add_another')</button> --}}

              <button type="submit" value="submit" class="btn btn-primary submit_product_form">@lang('messages.update')</button>
            </div>
          </div>
        </div>
  </div>
{!! Form::close() !!}
</section>
<!-- /.content -->

@endsection

@section('javascript')
  <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
@endsection
