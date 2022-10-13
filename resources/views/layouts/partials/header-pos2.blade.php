@inject('request', 'Illuminate\Http\Request')
<div class="col-md-12 no-print pos-header">
  <div class="row">

    <div class="col-md-10">

    
      <button style="font-size: 2rem;" type="button" id="close_register" title="{{ __('cash_register.close_register') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-danger btn-flat m-6 btn-xs m-5 btn-modal pull-right" data-container=".close_register_modal"
          data-href="{{ action('CashRegisterController@getCloseRegister')}}">
            <strong><i class="fa fa-window-close fa-lg"></i></strong>
      </button>

      <button style="font-size: 2rem;" type="button" id="register_details" title="{{ __('cash_register.register_details') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat m-6 btn-xs m-5 btn-modal pull-right" data-container=".register_details_modal"
          data-href="{{ action('CashRegisterController@getRegisterDetails')}}">
            <strong><i class="fa fa-briefcase fa-lg" aria-hidden="true"></i></strong>
      </button>

     

    

      {{-- <button style="font-size: 2rem;" type="button" id="view_suspended_sales" title="{{ __('lang_v1.view_suspended_sales') }}" data-toggle="tooltip" data-placement="bottom" class="btn bg-yellow btn-flat m-6 btn-xs m-5 btn-modal pull-right" data-container=".view_modal"
          data-href="{{ action('SellController@index')}}?suspended=1">
            <strong><i class="fa fa-pause-circle-o fa-lg"></i></strong>
      </button> --}}

    </div>

    {{-- <div class="col-md-2">
      <div class="m-6 pull-right mt-15 hidden-xs"><strong>{{ @format_date('now') }}</strong></div>
    </div> --}}

  </div>
</div>
