@inject('request', 'Illuminate\Http\Request')
<div class="col-md-12 no-print pos-header">
  <div class="row">

    <div class="col-md-10">

      <a style="font-size: 2rem; background: rgba(25, 0, 255, 0.808);" href="{{ action('SellPosController@index')}}" title="{{ __('lang_v1.go_back') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-info btn-flat m-6 btn-xs m-5 ">
        <strong><i class="fa fa-backward fa-lg"></i></strong>
      </a>

   

    </div>

    {{-- <div class="col-md-2">
      <div class="m-6 pull-right mt-15 hidden-xs"><strong>{{ @format_date('now') }}</strong></div>
    </div> --}}

  </div>
</div>
