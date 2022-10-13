
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

<div class="aiz-titlebar text-left mt-2 mb-3 container">
    <div class="align-items-center">
        <h1 class="h3">{{('All Customers')}}</h1>
    </div>
</div>


<div class="card container">
    <form class="" id="sort_customers" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col container">
                <h5 class="mb-0 h6">{{ ('Customers')}}</h5>
            </div>


            <div class="col-md-6">
              
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{  ('Type email or name & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <!--<th data-breakpoints="lg">#</th>-->
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{ ('Name')}}</th>
                        <th data-breakpoints="lg">{{ ('Email Address')}}</th>
                        <th data-breakpoints="lg">{{ ('Phone')}}</th>
                        <th data-breakpoints="lg">{{ ('Package')}}</th>
                        <th data-breakpoints="lg">{{ ('Wallet Balance')}}</th>
                        <th>{{ ('Options')}}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($customers as $key => $customer)
                        @if ($customer->user != null)
                            <tr>
                                <!--<td>{{ ($key+1) + ($customers->currentPage() - 1)*$customers->perPage() }}</td>-->
                                <td>
                                    <div class="form-group">
                                        <div class="aiz-checkbox-inline">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" class="check-one" name="id[]" value="{{$customer->id}}">
                                                <span class="aiz-square-check"></span>
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td>@if($customer->user->banned == 1) <i class="fa fa-ban text-danger" aria-hidden="true"></i> @endif {{$customer->user->name}}</td>
                                <td>{{$customer->user->email}}</td>
                                <td>{{$customer->user->phone}}</td>
                                <td>
                                    @if ($customer->user->customer_package != null)
                                    {{$customer->user->customer_package->getTranslation('name')}}
                                    @endif
                                </td>
                                <td>{{($customer->user->balance)}}</td>
                                <td class="text-right">
                                    <a
                                    {{-- href="{{route('customers.login', encrypt($customer->id))}}"  --}}


                                    class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{  ('Log in as this Customer') }}">
                                        <i class="las la-edit"></i>
                                    </a>
                                    @if($customer->user->banned != 1)
                                    <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm"


                                    {{-- onclick="confirm_ban('{{route('customers.ban', $customer->id)}}');"  --}}

                                    title="{{  ('Ban this Customer') }}">
                                        <i class="las la-user-slash"></i>
                                    </a>
                                    @else
                                    <a href="#" class="btn btn-soft-success btn-icon btn-circle btn-sm"

                                    {{-- onclick="confirm_unban('{{route('customers.ban', $customer->id)}}');" --}}

                                    title="{{  ('Unban this Customer') }}">
                                        <i class="las la-user-check"></i>
                                    </a>
                                    @endif
                                    <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"

                                    {{-- data-href="{{route('customers.destroy', $customer->id)}}" title="{{  ('Delete') }}" --}}


                                    >
                                        <i class="las la-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $customers->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>


<div class="modal fade" id="confirm-ban">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Confirmation')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ ('Do you really want to ban this Customer?')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
                <a type="button" id="confirmation" class="btn btn-primary">{{ ('Proceed!')}}</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm-unban">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Confirmation')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ ('Do you really want to unban this Customer?')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
                <a type="button" id="confirmationunban" class="btn btn-primary">{{ ('Proceed!')}}</a>
            </div>
        </div>
    </div>
</div>
@endsection



@section('script')
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

        function sort_customers(el){
            $('#sort_customers').submit();
        }
        function confirm_ban(url)
        {
            $('#confirm-ban').modal('show', {backdrop: 'static'});
            document.getElementById('confirmation').setAttribute('href' , url);
        }

        function confirm_unban(url)
        {
            $('#confirm-unban').modal('show', {backdrop: 'static'});
            document.getElementById('confirmationunban').setAttribute('href' , url);
        }

        function bulk_delete() {
            var data = new FormData($('#sort_customers')[0]);
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

