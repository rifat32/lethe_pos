
@extends('layouts.app')
<style>


    /* Set a style for all buttons */
    .modalbtn {
      background-color: #04AA6D;
      color: white;
      padding: 14px 20px;
      margin: 8px 0;
      border: none;
      cursor: pointer;
      width: 100%;
      opacity: 0.9;
    }

    .modalbtn:hover {
      opacity:1;
    }

    /* Float cancel and delete buttons and add an equal width */
    .cancelbtn, .deletebtn {
      float: left;
      width: 50%;
    }

    /* Add a color to the cancel button */
    .cancelbtn {
      background-color: #ccc;
      color: black;
    }

    /* Add a color to the delete button */
    .deletebtn {
      background-color: #f44336;
    }

    /* Add padding and center-align text to the container */
    .container2 {
      padding: 16px;
      text-align: center;
    }

    /* The Modal (background) */
    .modal2 {
      display: none; /* Hidden by default */
      position: fixed; /* Stay in place */
      z-index: 1; /* Sit on top */
      left: 0;
      top: 0;
      width: 100%; /* Full width */
      height: 100%; /* Full height */
      overflow: auto; /* Enable scroll if needed */
      background-color: #474e5d;
      padding-top: 50px;
    }

    /* Modal Content/Box */
    .modal-content2 {
      background-color: #fefefe;
      margin: 5% auto 15% auto; /* 5% from the top, 15% from the bottom and centered */
      border: 1px solid #888;
      width: 80%; /* Could be more or less, depending on screen size */
    }

    /* Style the horizontal ruler */
    hr {
      border: 1px solid #f1f1f1;
      margin-bottom: 25px;
    }

    /* The Modal Close Button (x) */
    .close2 {
      position: absolute;
      right: 35px;
      top: 15px;
      font-size: 40px;
      font-weight: bold;
      color: #f1f1f1;
    }

    .close2:hover,
    .close2:focus {
      color: #f44336;
      cursor: pointer;
    }

    /* Clear floats */
    .clearfix::after {
      content: "";
      clear: both;
      display: table;
    }

    /* Change styles for cancel button and delete button on extra small screens */
    @media screen and (max-width: 300px) {
      .cancelbtn, .deletebtn {
         width: 100%;
      }
    }
    </style>
@section('title', "orders")

@section('content')

<section class="content-header">
    <h1>Orders
        <small>Manage your orders</small>
    </h1>
    @if(Session::has('success'))
    <div class="alert alert-success">{{ Session::get('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
    </button>
    </div>
    @endif
</section>


<br>
<section class="content">
    <div class="card-header row gutters-5">
        <div class="col-sm-3 text-center">
            <h3 class="mb-md-0 h5">{{ ('All Orders') }}</h3>
        </div>
        <form class="" action="" id="sort_orders" method="GET">
        <div class="col-sm-2 ml-auto">
            <select class="form-control aiz-selectpicker" name="delivery_status" id="delivery_status">
                <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{('Pending')}}</option>
                <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>{{('Confirmed')}}</option>
                <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>{{('Picked Up')}}</option>
                <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>{{('On The Way')}}</option>
                <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{('Delivered')}}</option>
                <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>{{('Cancel')}}</option>
            </select>
        </div>
        <div class="col-sm-2">
            <div class="form-group mb-0">
                <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group mb-0">
                <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Type Order code & hit Enter') }}">
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
            </div>
        </div>

    </form>




    </div>

   <table class="table">
    <thead>
      <tr>
        {{-- <th>
            <div class="form-group">
                <div class="aiz-checkbox-inline">
                    <label class="aiz-checkbox">
                        <input type="checkbox" class="check-all">
                        <span class="aiz-square-check"></span>
                    </label>
                </div>
            </div>
        </th> --}}
        <th scope="col">Order Code</th>
        <th scope="col">Num. of Products</th>
        <th scope="col">Customer</th>
        <th scope="col">Amount</th>
        <th scope="col">Delivery Status</th>
        <th scope="col">Payment Status</th>
        <th scope="col">Options</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($orders as $key => $order)
                    <tr>
    <!--                    <td>
                            {{ ($key+1) + ($orders->currentPage() - 1)*$orders->perPage() }}
                        </td>-->
                        {{-- <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{$order->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td> --}}
                        <td>
                            {{ $order->code }}
                        </td>

                        <td>
                            {{ count($order->orderDetails) }}
                        </td>

                        <td>
                            @if ($order->user != null)
                            {{ $order->user->name }}
                            @else
                            Guest ({{ $order->guest_id }})
                            @endif
                        </td>
                        <td>
                            {{ $order->grand_total }}
                        </td>
                        <td>
                            @php
                                $status = $order->delivery_status;
                                if($order->delivery_status == 'cancelled') {
                                    $status = '<span class="badge badge-inline badge-danger">'."Cancel".'</span>';
                                }

                            @endphp
                            {!! $status !!}
                        </td>
                        <td>
                            @if ($order->payment_status == 'paid')
                            <span class="badge badge-inline badge-success">Paid</span>
                            @else
                            <span class="badge badge-inline badge-danger">Unpaid</span>
                            @endif
                        </td>
                        {{-- @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                        <td>
                            @if (count($order->refund_requests) > 0)
                            {{ count($order->refund_requests) }} Refund
                            @else
                            No Refund
                            @endif
                        </td>
                        @endif --}}
                        <td class="text-left">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                              href="{{route('orders.show', encrypt($order->id))}}"
                             title="View">
                             <i class="fa fa-eye"></i>

                            </a>
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                             href="{{ route('order.invoice.download', $order->id) }}"
                             title="Download Invoice">
                                <i class="fa fa-download"></i>
                            </a>
                            <a href="#" id="deleteOrder" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                         onclick="func('{{route('orders.destroy', $order->id)}}')"
                            data-href="{{route('orders.destroy', $order->id)}}"
                                 title="Delete">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
    </tbody>
  </table>
  <div class="aiz-pagination">
    {{ $orders->appends(request()->input())->links() }}
</div>
</section>
<div id="id01" class="modal2">
    <span onclick="document.getElementById('id01').style.display='none'" class="close2" title="Close Modal">×</span>
    <form class="modal-content2" id="submit_delete" method="POST" >
        {!! method_field('delete') !!}
        {!! csrf_field() !!}
      <div class="container2">
        <h1>Delete Account</h1>
        <p>Are you sure you want to delete your account?</p>

        <div class="clearfix">
          <button type="button" onclick="document.getElementById('id01').style.display='none'" class="modalbtn cancelbtn">Cancel</button>
          <button type="submit" onclick="document.getElementById('id01').style.display='none'" class="modalbtn deletebtn">Delete</button>
        </div>
      </div>
    </form>
  </div>

<script>
   function func(link){
    document.getElementById('id01').style.display='block';
    document.getElementById('submit_delete').setAttribute("action", link)

    console.log(link)

   }
// Get the modal
var modal = document.getElementById('id01');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>

  </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script type="text/javascript">
    $(document).on("change", ".check-all", function() {
        if(this.checked) {
            // Iterate each checkbox
            $('.check-one:checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $('.check-one:checkbox').each(function() {
                this.checked = false;
            });
        }

    });

//        function change_status() {
//            var data = new FormData($('#order_form')[0]);
//            $.ajax({
//                headers: {
//                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                },
//
//                type: 'POST',
//                data: data,
//                cache: false,
//                contentType: false,
//                processData: false,
//                success: function (response) {
//                    if(response == 1) {
//                        location.reload();
//                    }
//                }
//            });
//        }

    function bulk_delete() {
        var data = new FormData($('#sort_orders')[0]);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            type: 'POST',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                if(response == 1) {
                    location.reload();
                }
            }
        });
    }
</script>
@endsection

