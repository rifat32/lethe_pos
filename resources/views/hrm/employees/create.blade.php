@extends('layouts.app')

@section('title',  'HRM Employee' )

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Add Employee</h1>
</section>

<!-- Main content -->
<section class="content">
<div class="box">
    <div class="box-body">
    <div class="row">
    {!! Form::open(['url' => action('HrmController@store'), 'method' => 'post', 'id' => 'user_add_form' ,'files' => true]) !!}
      <div class="col-md-2">
        <div class="form-group">
        {!! Form::label('image', __('Employee Image') . ':') !!}
            {!! Form::file('image', ['id' => 'upload_image', 'accept' => 'image/*']); !!}
            <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p></small>
        </div>
      </div>
      <div class="clearfix"></div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('name', __( 'Name' ) . ':*') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'name' ) ]); !!}
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('father_name', __( 'Father Name' ) . ':') !!}
            {!! Form::text('father_name', null, ['class' => 'form-control', 'placeholder' => __( 'Father Name' ) ]); !!}
          
        </div>
      </div>
      <div class="clearfix"></div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('dob', __( 'Date Of Birth' ) . ':*') !!}
            {!! Form::date('dob', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'Date Of Birth' ) ]); !!}
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('gender', __('Gender' ) . ':') !!}
            {!! Form::select('gender', ['male' => 'Male', 'female' => 'Female'], null, ['class' => 'form-control', 'placeholder' => __( 'Gender' ) ]); !!}
        </div>
      </div>
      <div class="clearfix"></div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('phone', __( 'Phone' ) . ':*') !!}
            {!! Form::text('phone', null, ['class' => 'form-control' , 'placeholder' => __( 'Phone' ) ]); !!}
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('address', __( 'Address' ) . ':*') !!}
            {!! Form::text('address', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'Address' ) ]); !!}
        </div>
      </div>
    
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('p_address', __( 'Permanent Address' ) . ':*') !!}
            {!! Form::text('p_address', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'Permanent Address' ) ]); !!}
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('employee_id', __( 'Employee ID' ) . ':') !!}
            {!! Form::text('employee_id', null, ['class' => 'form-control', 'placeholder' => __( 'Employee ID' )]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('department', __( 'Department' ) . ':') !!}
            {!! Form::text('department', null, ['class' => 'form-control', 'placeholder' => __( 'Department' )]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('designation', __( 'Designation' ) . ':') !!}
            {!! Form::text('designation', null, ['class' => 'form-control', 'placeholder' => __( 'Designation' )]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('salary', __( 'Salary' ) . ':') !!}
            {!! Form::text('salary', null, ['class' => 'form-control', 'placeholder' => __( 'Salary' )]); !!}
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('doj', __( 'Join Date' ) . ':') !!}
            {!! Form::date('doj', null, ['class' => 'form-control', 'placeholder' => __( 'Join Date' )]); !!}
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('exit_date', __( 'Exit Date' ) . ':') !!} 
            {!! Form::date('exit_date', null, ['class' => 'form-control', 'placeholder' => __( 'Exit Date' )]); !!}
        </div>
      </div>
      <div class="col-md-6" hidden>
        <div class="form-group">
          {!! Form::label('status', __( 'Status' ) . ':') !!}
            {!! Form::text('status', 0, ['class' => 'form-control', 'placeholder' => __( 'Status' )]); !!}
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

  $('form#user_add_form').validate({
                rules: {
                    first_name: {
                        required: true,
                    },
                    email: {
                        email: true
                    },
                    password: {
                        required: true,
                        minlength: 5
                    },
                    confirm_password: {
                        equalTo: "#password"
                    },
                    username: {
                        required: true,
                        minlength: 5,
                        remote: {
                            url: "/business/register/check-username",
                            type: "post",
                            data: {
                                username: function() {
                                    return $( "#username" ).val();
                                },
                                @if(!empty($username_ext))
                                  username_ext: "{{$username_ext}}"
                                @endif
                            }
                        }
                    }
                },
                messages: {
                    password: {
                        minlength: 'Password should be minimum 5 characters',
                    },
                    confirm_password: {
                        equalTo: 'Should be same as password'
                    },
                    username: {
                        remote: 'Invalid username or User already exist'
                    }
                }
            });

</script>
@endsection
