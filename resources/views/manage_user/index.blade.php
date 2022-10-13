@extends('layouts.app')
@section('title', 'Users')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'user.users' )
        <small>@lang( 'user.manage_users' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">@lang( 'user.all_users' )</h3>
            @can('user.create')
            	<div class="box-tools">
                    <a class="btn btn-block btn-primary" 
                    	href="{{action('ManageUserController@create')}}" >
                    	<i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                </div>
            @endcan
        </div>
        <div class="box-body">
            @can('user.view')
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" id="users_table">
        		<thead>
        			<tr>
        				<th>@lang( 'user.name' )</th>
                        <th>@lang( 'business.username' )</th>
                        <th>@lang( 'user.role' )</th>
                        <th>@lang( 'business.email' )</th>
                        <th>Salary</th>
        				<th>@lang( 'messages.action' )</th>
        			</tr>
        		</thead>
        	</table>
            </div>
            @endcan
        </div>
    </div>

    <div class="modal fade user_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    //Roles table
    $(document).ready( function(){
        var users_table = $('#users_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/users',
                    columnDefs: [ {
                        "targets": [4],
                        "orderable": false,
                        "searchable": false
                    } ],
                    "columns":[
                        {"data":"full_name"},
                        {"data":"username"},
                        {"data":"role"},
                        {"data":"email"},
                        {"data":"salary"},
                        {"data":"action"}
                    ]
                });
        $(document).on('click', 'button.delete_user_button', function(){
            swal({
              title: LANG.sure,
              text: LANG.confirm_delete_user,
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                users_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
             });
        });
        
    });
    
    
</script>
@endsection
