@extends('layouts.app')
@section('title', 'Hrm|Employee')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Employees
        <small>Manage Employees</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">All Employees</h3>
            @can('hrm_employyes.create')
            	<div class="box-tools">
                    <a class="btn btn-block btn-primary" 
                    	href="{{action('HrmController@create')}}" >
                    	<i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                </div>
            @endcan
        </div>
        <div class="box-body">
            @can('hrm_employyes.view')
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" id="hrm_employee_table">
        		<thead>
        			<tr>
        				<th>#</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Joinied</th>
                        <th>Salary</th>
                        <th>Status</th>                      
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
        var hrm_employee_table = $('#hrm_employee_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/hrm_employee',
                    columnDefs: [ {
                        "targets": [4],
                        "orderable": false,
                        "searchable": false
                    } ]
                });
                $(document).on('submit', 'form#user_add_form', function(e){
		e.preventDefault();
		var data = $(this).serialize();
		$.ajax({
		method: "POST",
		url: $(this).attr("action"),
		dataType: "json",
		data: data,
		success: function(result){
			if(result.success === true){
				$('div.user_modal').modal('hide');
				toastr.success(result.msg);
				hrm_employee_table.ajax.reload();
			} else {
				toastr.error(result.msg);
			}
		}
		});
		});
        $(document).on('click', 'button.delete_hrm_employee_button', function(){
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
                                hrm_employee_table.ajax.reload();
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
