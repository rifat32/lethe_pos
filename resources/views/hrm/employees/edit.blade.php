@extends('layouts.app')

@section('title',  'HRM Employee' )

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Update Employee</h1>
</section>

<!-- Main content -->
<section class="content">
<div class="box">
    <div class="box-body">
    <div class="row">
    {!! Form::open(['url' => action('HrmController@update', [$expense_category->id]), 'method' => 'PUT', 'id' => 'user_add_form' ]) !!}
      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('image', __( 'Image' ) . ':') !!}
            {!! Form::file('image', null, ['class' => 'form-control', 'placeholder' => __( 'Image' ) ]); !!}
        </div>
      </div>
      <div class="col-md-5">
        <div class="form-group">
          {!! Form::label('name', __( 'Name' ) . ':*') !!}
            {!! Form::text('name',  $expense_category->name, ['class' => 'form-control', 'required', 'placeholder' => __( 'name' ) ]); !!}
        </div>
      </div>
      <div class="col-md-5">
        <div class="form-group">
          {!! Form::label('father_name', __( 'Father Name' ) . ':') !!}
            {!! Form::text('father_name',  $expense_category->father_name, ['class' => 'form-control', 'placeholder' => __( 'Father Name' ) ]); !!}
        </div>
      </div>
      <div class="clearfix"></div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('dob', __( 'Date Of Birth' ) . ':*') !!}
            {!! Form::date('dob',  $expense_category->dob, ['class' => 'form-control', 'required', 'placeholder' => __( 'Date Of Birth' ) ]); !!}
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('gender', __('Gender' ) . ':') !!}
            {!! Form::select('gender',['male' => 'Male', 'female' => 'Female'],  $expense_category->gender, ['class' => 'form-control', 'placeholder' => __( 'Gender' ) ]); !!}
        </div>
      </div>
      <div class="clearfix"></div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('phone', __( 'Phone' ) . ':*') !!}
            {!! Form::text('phone',  $expense_category->phone, ['class' => 'form-control' , 'placeholder' => __( 'Phone' ) ]); !!}
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('address', __( 'Address' ) . ':*') !!}
            {!! Form::text('address',  $expense_category->address, ['class' => 'form-control', 'required', 'placeholder' => __( 'Address' ) ]); !!}
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('p_address', __( 'Permanent Address' ) . ':*') !!}
            {!! Form::text('p_address',  $expense_category->p_address, ['class' => 'form-control', 'required', 'placeholder' => __( 'Permanent Address' ) ]); !!}
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('employee_id', __( 'Employee ID' ) . ':') !!}
            {!! Form::text('employee_id',  $expense_category->employee_id, ['class' => 'form-control', 'placeholder' => __( 'Employee ID' )]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('department', __( 'Department' ) . ':') !!}
            {!! Form::text('department',  $expense_category->department, ['class' => 'form-control', 'placeholder' => __( 'Department' )]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('designation', __( 'Designation' ) . ':') !!}
            {!! Form::text('designation',  $expense_category->designation, ['class' => 'form-control', 'placeholder' => __( 'Designation' )]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('doj', __( 'Join Date' ) . ':') !!}
            {!! Form::date('doj',  $expense_category->doj, ['class' => 'form-control', 'placeholder' => __( 'Join Date' )]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('exit_date', __( 'Exit Date' ) . ':') !!} 
            {!! Form::date('exit_date',  $expense_category->exit_date, ['class' => 'form-control', 'placeholder' => __( 'Exit Date' )]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('status', __( 'Status' ) . ':') !!}
            {!! Form::text('status',  $expense_category->status, ['class' => 'form-control', 'placeholder' => __( 'Status' )]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('salary', __( 'Salary' ) . ':') !!}
            {!! Form::text('salary',  $expense_category->salary, ['class' => 'form-control', 'placeholder' => __( 'Salary' )]); !!}
        </div>
      </div>

    </div>
    </div>
    <div class="row">
     <div class="col-md-12">
      <button type="submit" class="btn btn-primary pull-right" id="submit_user_button">@lang( 'messages.save' )</button>
      </div>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
  @stop
@section('javascript')
<script type="text/javascript">
  $(document).ready(function(){
    $('#selected_contacts').on('ifChecked', function(event){
      $('div.selected_contacts_div').removeClass('hide');
    });
    $('#selected_contacts').on('ifUnchecked', function(event){
      $('div.selected_contacts_div').addClass('hide');
    });
  });

//   $('form#user_add_form').validate({
//                 rules: {
//                     first_name: {
//                         required: true,
//                     },
//                     email: {
//                         email: true
//                     },
//                     password: {
//                         required: true,
//                         minlength: 5
//                     },
//                     confirm_password: {
//                         equalTo: "#password"
//                     },
//                     username: {
//                         required: true,
//                         minlength: 5,
//                         remote: {
//                             url: "/business/register/check-username",
//                             type: "post",
//                             data: {
//                                 username: function() {
//                                     return $( "#username" ).val();
//                                 },
//                                 @if(!empty($username_ext))
//                                   username_ext: "{{$username_ext}}"
//                                 @endif
//                             }
//                         }
//                     }
//                 },
//                 messages: {
//                     password: {
//                         minlength: 'Password should be minimum 5 characters',
//                     },
//                     confirm_password: {
//                         equalTo: 'Should be same as password'
//                     },
//                     username: {
//                         remote: 'Invalid username or User already exist'
//                     }
//                 }
//             });

</script>
@endsection
