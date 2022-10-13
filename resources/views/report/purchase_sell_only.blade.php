@extends('layouts.app')
@section('title', __( 'report.purchase_sell' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> Sell Report 
        <small>Daily Sell Report</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="print_section"><h2>{{session()->get('business.name')}} - @lang( 'report.purchase_sell' )</h2></div>
    <div class="row no-print">
        <div class="col-md-3 col-md-offset-7 col-xs-6">
            <div class="input-group">
                <span class="input-group-addon bg-light-blue"><i class="fa fa-map-marker"></i></span>
                 <select class="form-control select2" id="purchase_sell_location_filter">
                    @foreach($business_locations as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2 col-xs-6">
            <div class="form-group pull-right">
                <div class="input-group">
                  <button type="button" class="btn btn-primary" id="purchase_sell_date_filter">
                    <span>
                      <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                    </span>
                    <i class="fa fa-caret-down"></i>
                  </button>
                </div>
            </div>
        </div>
    </div>
    <br>


    <div class="row">
       {{--   <div class="col-xs-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('purchase.purchases') }}</h3>
                </div>

                <div class="box-body">
                    <table class="table table-striped">
                        <tr>
                            <th>{{ __('report.total_purchase') }}:</th>
                            <td>
                                <span class="total_purchase">
                                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('report.purchase_inc_tax') }}:</th>
                            <td>
                                 <span class="purchase_inc_tax">
                                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('lang_v1.total_purchase_return_inc_tax') }}:</th>
                            <td>
                                 <span class="purchase_return_inc_tax">
                                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('report.purchase_due') }}: @show_tooltip(__('tooltip.purchase_due'))</th>
                            <td>
                                 <span class="purchase_due">
                                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
  --}}

        <div class="col-xs-10" style="margin-left:5%;">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('sale.sells') }}</h3>
                </div>

                <div class="box-body">
                    <table class="table table-striped">
                        <tr>
                            <th>{{ __('report.total_sell') }}:</th>
                            <td>
                                <span class="total_sell">
                                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('report.sell_inc_tax') }}:</th>
                            <td>
                                 <span class="sell_inc_tax">
                                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                                </span>
                            </td>
                        </tr>
                       {{--   <tr>
                            <th>{{ __('lang_v1.total_sell_return_inc_tax') }}:</th>
                            <td>
                                 <span class="total_sell_return">
                                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('report.sell_due') }}: @show_tooltip(__('tooltip.sell_due'))</th>
                            <td>
                                <span class="sell_due">
                                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                                </span>
                            </td>
                        </tr>  --}}
                    </table>
                </div>
            </div>
        </div>
    </div>

   

    <div class="row no-print">
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" onclick="window.print();"
            ><i class="fa fa-print"></i> @lang( 'messages.print' )</button>
        </div>
    </div>
	

</section>
<!-- /.content -->
@stop
@section('javascript')
<script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>

@endsection
