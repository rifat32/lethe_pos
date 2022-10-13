
@extends('layouts.app')
@section('title', "orders")

@section('content')

<section class="content-header">
    <h1>
        Details
    </h1>

</section>

<div class="card container" style="width:90%;">
    <div class="card-header">
        <h1 class="h2 fs-16 mb-0">Order Details</h1>
    </div>
    <div class="card-body">
        <div class="row gutters-5">
            <div class="col text-center text-md-left">
            </div>
            @php
            $delivery_status = $order->delivery_status;
            $payment_status = $order->payment_status;
            @endphp


<div class="col-md-3">

</div>
@php
    $delivery_mans = [
        ["name"=>"Test"],
        ["name"=>"Test2"],
];
@endphp
<div class="col-md-3 ml-auto">
    <label for="update_delivery_man">Delivery Man</label>

        <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_delivery_status">
            <option value="" @if ($order->delivery_man == '') selected @endif>Please Select</option>
            @foreach ($delivery_mans as  $delivery_man)
            <option value="{{$delivery_man["name"]}}" @if ($delivery_man["name"] == $order->delivery_man ) selected @endif>{{$delivery_man["name"]}}</option>
   @endforeach

        </select>


</div>



            <div class="col-md-3 ml-auto">
                <label for="update_payment_status">Payment Status</label>
                <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_payment_status">
                    <option value="unpaid" @if ($payment_status == 'unpaid') selected @endif>Unpaid</option>
                    <option value="paid" @if ($payment_status == 'paid') selected @endif>Paid</option>
                </select>
            </div>
            <div class="col-md-3 ml-auto">
                <label for="update_delivery_status">Delivery Status</label>
                @if($delivery_status != 'delivered' && $delivery_status != 'cancelled' && $delivery_status != 'return')
                    <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_delivery_status">
                        <option value="pending" @if ($delivery_status == 'pending') selected @endif>Pending</option>
                        <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>Confirmed</option>

                        <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>On The Way</option>
                        <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>Delivered</option>
                        <option value="return" @if ($delivery_status == 'return') selected @endif>Return</option>
                        <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>Cancel</option>
                    </select>
                @else
                    <input type="text" class="form-control" value="{{ $delivery_status }}" disabled>
                @endif
            </div>
        </div>
        <div class="row gutters-5">
            <div class="col-md-4 text-center text-md-left">
                <address>
                    <strong class="text-main">{{ json_decode($order->shipping_address)->name }}</strong><br>
                    {{ json_decode($order->shipping_address)->email }}<br>
                    {{ json_decode($order->shipping_address)->phone }}<br>
                    {{ json_decode($order->shipping_address)->alternative_phone_number }}<br>
                    {{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }},
                    {{-- {{ json_decode($order->shipping_address)->postal_code }}<br> --}}
                    {{ json_decode($order->shipping_address)->country }}
                </address>

                @if ($order->manual_payment && is_array(json_decode($order->manual_payment_data, true)))
                <br>
                <strong class="text-main">Payment Information</strong><br>
                Name: {{ json_decode($order->manual_payment_data)->name }}, Amount: {{ json_decode($order->manual_payment_data)->amount }}, TRX ID
                : {{ json_decode($order->manual_payment_data)->trx_id }}
                <br>
                <a href="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" target="_blank"><img src="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}" alt="" height="100"></a>
                @endif
            </div>
            <div class="col-md-4 ml-auto"></div>
            <div class="col-md-4 ml-auto">
                <table>
                    <tbody>
                        <tr>
                            <td class="text-main text-bold">Order #</td>
                            <td class="text-right text-info text-bold">	{{ $order->code }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">Order Status</td>
                            <td class="text-right">
                                @if($delivery_status == 'delivered')
                                <span class="badge badge-inline badge-success">{{ ucfirst(str_replace('_', ' ', $delivery_status)) }}</span>
                                @else
                                <span class="badge badge-inline badge-info">{{ ucfirst(str_replace('_', ' ', $delivery_status)) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">Order Date	</td>
                            <td class="text-right">{{ date('d-m-Y h:i A', $order->date) }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">
                                Total amount
                            </td>
                            <td class="text-right">
                                {{$order->grand_total}}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">Payment method</td>
                            <td class="text-right">{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <hr class="new-section-sm bord-no">
        <div class="row">
            <div class="col-lg-12 table-responsive">
                <table class="table table-bordered aiz-table invoice-summary">
                    <thead>
                        <tr class="bg-trans-dark">
                            <th data-breakpoints="lg" class="min-col">#</th>
                            <th width="10%">Photo</th>
                            <th class="text-uppercase">Description</th>
                            <th data-breakpoints="lg" class="text-uppercase">Delivery Type</th>
                            <th data-breakpoints="lg" class="min-col text-center text-uppercase">Qty</th>
                            <th data-breakpoints="lg" class="min-col text-center text-uppercase">Price</th>
                            <th data-breakpoints="lg" class="min-col text-right text-uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->orderDetails as $key => $orderDetail)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>
                                @if ($orderDetail->product != null)
                                <a href="{{ route('product.view', $orderDetail->product->id) }}" target="_blank"><img height="50" src="{{ asset(('/storage/img/' . $orderDetail->product->product->image)) }}"></a>
                                @else
                                <strong>N/A</strong>
                                @endif
                            </td>
                            <td>
                                @if ($orderDetail->product != null)
                                <strong><a href="{{ route('product.view', $orderDetail->product->id) }}" target="_blank" class="text-muted">{{ $orderDetail->product->product->name }}</a></strong>
                                <small>{{ $orderDetail->product->name }} </small>
                                @else
                                <strong>Product Unavailable</strong>
                                @endif
                            </td>
                            <td>

                                @if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
                               Home Delivery

                                @elseif ($orderDetail->shipping_type == 'pickup_point')

                                @if ($orderDetail->pickup_point != null)
                                {{ $orderDetail->pickup_point->getTranslation('name') }} ({{ translate('Pickup Point') }})
                                @else
                                {{ translate('Pickup Point') }}
                                @endif
                                @endif
                            </td>
                            <td class="text-center">{{ $orderDetail->quantity }}</td>

                            <td class="text-center">{{ $orderDetail->price/$orderDetail->quantity }}</td>
                            <td class="text-center">{{ $orderDetail->price }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="clearfix float-right row">
            <div class="col-md-8"></div>
            <div class="col-md-3">
                <table class="table text-center">
                    <tbody>
                        <tr>
                            <td>
                                <strong class="text-muted">Sub Total :</strong>
                            </td>
                            <td>
                                {{$order->orderDetails->sum('price') }}
                            </td>
                        </tr>
                        <tr>

                            <td>
                                <strong class="text-muted">Tax :</strong>
                            </td>
                            <td>
                                {{ $order->orderDetails->sum('tax') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">Shipping :</strong>
                            </td>
                            <td id="shipping_table">
                                {{ $order->shipping + $order->area_shipping }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">Coupon:</strong>
                            </td>
                            <td>
                                {{ $order->coupon_discount }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">TOTAL :</strong>
                            </td>

                            <td class="text-muted h5" id="total">
                                {{ $order->grand_total + $order->shipping + $order->area_shipping   }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                @php
          $charts = [

             [
                 "name"=> "0-2 kg 120tk",
                 "value" => "120"
            ],
                 [
                 "name"=> "2.1-5 kg 150tk",
                 "value" => "150"
            ],
                 [
                 "name"=> "5.1-8 kg 200tk",
                 "value" => "200"
            ],
                 [
                 "name"=> "8.1-12 kg 250tk",
                 "value" => "250"
            ],
                 [
                 "name"=> "12.1-20 kg 300tk",
                 "value" => "300"
            ],
                //  [
                //  "name"=> "20 kg+ Negotiable",
                //  "value" => "0"
                //  ]


            ];
          $areas = [

[
    "name"=> "Inside Dhaka 80TK",
    "value" => "80"
],
[
    "name"=> "Out Side Dhaka ",
    "value" => "0"
],



            ];
                @endphp


            </div>
            <div class="row">
                <div class="col-md-8">

                </div>
                <div class="col-md-3 ml-auto">
                    <label for="update_shipping">Shipping</label>
                    <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_shipping">
                        <option value="0" @if ($order->shipping == 0) selected @endif>Please Select</option>
@foreach ($charts as $chart)
<option value="{{$chart["value"]}}" @if ($order->shipping == $chart["value"]) selected @endif>{{$chart["name"]}}</option>
@endforeach



                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">

                </div>
                <div class="col-md-3 ml-auto">
                    <label for="update_area_shipping">Area</label>
                    <select class="form-control aiz-selectpicker"  data-minimum-results-for-search="Infinity" id="update_area_shipping">

@foreach ($areas as $area)
<option value="{{$area["value"]}}" @if ($order->area_shipping == $area["value"]) selected @endif>{{$area["name"]}}</option>
@endforeach



                    </select>
                </div>
            </div>

            <div class="row" style="margin-top: 1rem" onclick="update()">
                <div class="col-md-8">

                </div>
                <div class="col-md-3 ml-auto">
                   <button class="btn btn-primary">
                       Update Info
                   </button>
                </div>
            </div>

            {{-- <div class="text-right no-print">
                <a href="{{ route('invoice.download', $order->id) }}" type="button" class="btn btn-icon btn-light"><i class="las la-print"></i></a>
            </div> --}}
        </div>

    </div>
</div>

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script type="text/javascript">
$('#update_area_shipping').on('change', function(){
    var shipping = $('#update_shipping').val();
    var area_shipping = $('#update_area_shipping').val();
    if(area_shipping == 80) {
        document.getElementById("update_shipping").value = 0
    }


    });
    $('#update_shipping').on('change', function(){
    var shipping = $('#update_shipping').val();
    var area_shipping = $('#update_area_shipping').val();
    if(area_shipping !== 0) {
        document.getElementById("update_area_shipping").value = 0
    }


    });
const update = () => {
    var order_id = {{ $order->id }};
        var delivery_status = $('#update_delivery_status').val();
        $.post('{{ route('orders.update_delivery_status') }}', {
            _token:'{{ @csrf_token() }}',
            order_id:order_id,
            status:delivery_status
        }, function(data){

        });

        var payment_status = $('#update_payment_status').val();
        $.post('{{ route('orders.update_payment_status') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:payment_status}, function(data){

        });
        var shipping = $('#update_shipping').val();
        var area_shipping = $('#update_area_shipping').val();
        var delivery_man = $('#update_delivery_man').val();

        // $.post('{{ route('orders.update_area_shipping') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:area_shipping}, function(data){
        //     document.getElementById("shipping_table").innerHTML = `${parseInt(data.shipping) + parseInt(data.area_shipping)}tk`;
        //     document.getElementById("total").innerHTML = `${parseInt(data.shipping) + parseInt(data.area_shipping) + parseInt(data.grand_total)}tk`;
        //     toastr.success("Data Updated");

        // });

        $.post('{{ route('orders.update_shipping') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,shipping:shipping,area_shipping:area_shipping,delivery_man:delivery_man}, function(data){

            document.getElementById("shipping_table").innerHTML = `${parseInt(data.shipping) + parseInt(data.area_shipping)}tk`;
            document.getElementById("total").innerHTML = `${parseInt(data.shipping) + parseInt(data.area_shipping) + parseInt(data.grand_total)}tk`;
            document.getElementById("update_shipping").value = data.shipping
            document.getElementById("update_area_shipping").value = data.area_shipping

        });



}
    // $('#update_delivery_status').on('change', function(){

    //     var order_id = {{ $order->id }};
    //     var status = $('#update_delivery_status').val();
    //     $.post('{{ route('orders.update_delivery_status') }}', {
    //         _token:'{{ @csrf_token() }}',
    //         order_id:order_id,
    //         status:status
    //     }, function(data){

    //     });

    // });

    // $('#update_payment_status').on('change', function(){
    //     var order_id = {{ $order->id }};
    //     var status = $('#update_payment_status').val();
    //     $.post('{{ route('orders.update_payment_status') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){
    //         AIZ.plugins.notify('success', 'Payment status has been updated');
    //     });
    // });

    // $('#update_shipping').on('change', function(){
    //     var order_id = {{ $order->id }};
    //     var status = $('#update_shipping').val();
    //     $.post('{{ route('orders.update_shipping') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){

    //         document.getElementById("shipping_table").innerHTML = `${parseInt(data.shipping) + parseInt(data.area_shipping)}tk`;
    //         document.getElementById("total").innerHTML = `${parseInt(data.shipping) + parseInt(data.area_shipping) + parseInt(data.grand_total)}tk`;
    //         AIZ.plugins.notify('success', 'Payment status has been updated');
    //     });
    // });

    // $('#update_area_shipping').on('change', function(){
    //     var order_id = {{ $order->id }};
    //     var status = $('#update_area_shipping').val();
    //     $.post('{{ route('orders.update_area_shipping') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){

    //         document.getElementById("shipping_table").innerHTML = `${parseInt(data.shipping) + parseInt(data.area_shipping)}tk`;
    //         document.getElementById("total").innerHTML = `${parseInt(data.shipping) + parseInt(data.area_shipping) + parseInt(data.grand_total)}tk`;
    //         AIZ.plugins.notify('success', 'Payment status has been updated');
    //     });
    // });
</script>
@endsection

@endsection

