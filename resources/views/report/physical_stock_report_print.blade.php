
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr'}}">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/pace/pace.css?v='.$asset_v) }}">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset('plugins/font-awesome/css/font-awesome.min.css?v='.$asset_v) }}">
        
        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('plugins/jquery-ui/jquery-ui.min.css?v='.$asset_v) }}">
        <!-- Bootstrap 3.3.6 -->
        <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css?v='.$asset_v) }}">
        
        @if( in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) )
            <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.rtl.min.css?v='.$asset_v) }}">
        @endif
        
        <!-- Ionicons -->
        <link rel="stylesheet" href="{{ asset('plugins/ionicons/css/ionicons.min.css?v='.$asset_v) }}">
         <!-- Select2 -->
        <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/select2/select2.min.css?v='.$asset_v) }}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset('AdminLTE/css/AdminLTE.min.css?v='.$asset_v) }}">
        <!-- iCheck -->
        <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/iCheck/square/blue.css?v='.$asset_v) }}">
        
        <!-- bootstrap datepicker -->
        <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/datepicker/bootstrap-datepicker.min.css?v='.$asset_v) }}">
        
        <!-- DataTables -->
        <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/DataTables/datatables.min.css?v='.$asset_v) }}">
        
        <!-- Toastr -->
        <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css?v='.$asset_v) }}">
        <!-- Bootstrap file input -->
        <link rel="stylesheet" href="{{ asset('plugins/bootstrap-fileinput/fileinput.min.css?v='.$asset_v) }}">
        
        <!-- AdminLTE Skins.-->
        <link rel="stylesheet" href="{{ asset('AdminLTE/css/skins/_all-skins.min.css?v='.$asset_v) }}">
        
        @if( in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) )
            <link rel="stylesheet" href="{{ asset('AdminLTE/css/AdminLTE.rtl.min.css?v='.$asset_v) }}">
        @endif
        
        <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/daterangepicker/daterangepicker.css?v='.$asset_v) }}">
        <link rel="stylesheet" href="{{ asset('plugins/bootstrap-tour/bootstrap-tour.min.css?v='.$asset_v) }}">
        <link rel="stylesheet" href="{{ asset('plugins/calculator/calculator.css?v='.$asset_v) }}">
        
        <link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">
        
        @yield('css')
        <!-- app css -->
        <link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">
        
        @if(isset($pos_layout) && $pos_layout)
            <style type="text/css">
                .content{
                    padding-bottom: 0px !important;
                }
            </style>
        @endif

        @yield('css')
    </head>

    <body>

       

        <section class="content">

           <div style="text-align: center;"><p>Bismillahir Rahmanir Rahim</p><div class="text-center" style="display: flex;"><img src="https://excellentfood.com.bd/master/storage//lL1n0sMQC94BPXAZ2FGH4x7kUKUqO9bnvxZNjpVD.jpeg" class="product-thumbnail-small"></div><h1 style="color: #16a021;">Main Shop</h1><h5 style="color: #1626a0;">Office: Dhaka Dhaka 1207 Bangladesh</h5><h4 style="color: #e20a0a;"> 01719131305  </h4></div>


      
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>@lang('business.product')</th>
                                <th>Current Stock <br/><small>(balancing time)</small></th>
                                <th>Balance</th>
                                <th>Physical Stock</th>
                                <th>Created By</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $item)     
                            <tr>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->name }}</td>
                                <td>
                                    {{ $item->current_stock }}
                                </td>
                                <td>
                                    {{ $item->current_stock - $item->physical_qty }}
                                </td>
                                <td>
                                    {{ $item->physical_qty }}
                                </td>
                               
                                <td>
                                    {{ ucfirst($item->users->username) }}
                                </td>
                                <td>
                                    {{ date('m/d/Y',strtotime($item->created_at)) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray font-17  footer-total">
                                <td colspan="1"><strong>@lang('sale.total'):</strong></td>
                                <td ></td>
                                <td >{{ $total_current_stock }}</td>
                                <td ></td>
                                <td >{{ $total }}</td>
                                <td ></td>
                                <td ></td>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
            <div style="text-align: right;"><h5 style="margin-right: 9%;">Main Shop<br>2020-07-21 15:56:36</h5><p style="width: 12%;height: 2px;background: #000;float: right;margin-right: 9%;"></p><h5 style="margin-right: -11%;float: right;">Authorized by</h5></div>
        </section>


        <script type="text/javascript">
            base_path = "{{url('/')}}";
        </script>

        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js?v=$asset_v"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js?v=$asset_v"></script>
        <![endif]-->

        <script src="{{ asset('AdminLTE/plugins/pace/pace.min.js?v=' . $asset_v) }}"></script>

        <!-- jQuery 2.2.3 -->
        <script src="{{ asset('AdminLTE/plugins/jQuery/jquery-2.2.3.min.js?v=' . $asset_v) }}"></script>
        <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js?v=' . $asset_v) }}"></script>
        <!-- Bootstrap 3.3.6 -->
        <script src="{{ asset('bootstrap/js/bootstrap.min.js?v=' . $asset_v) }}"></script>
        <!-- iCheck -->
    </body>

</html>
