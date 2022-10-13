@extends('layouts.app')

@section('title', __( 'Give Attendence' ))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-11">
            
            <div class="panel panel-default">
                <div class="panel-heading">Attendance</div>
                @if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
    @endif
    <?php
    $dt = new DateTime();
    ?>
            @if($hrmd)
            <div class="panel-body">
                <h1 class="text-center">Todays Attendence is Given!</h1>
                </div>
            @else
                <div class="panel-body">
                    
                    <form class="form-horizontal" role="form" method="POST" action="{{ action('HrmAttendenceController@store') }}">
                        {{ csrf_field() }}
                        <div class="form-group{{ $errors->has('date') ? ' has-error' : '' }}">
                            <label for="date" class="col-md-1 control-label">Date</label>
                            <div class="col-md-11">
                            <!-- {!! Form::date('date', null, ['class' => 'form-control', 'placeholder' => __( 'Date' )]); !!} -->
                            {!! Form::text('date', @format_date('now'), ['class' => 'form-control', 'readonly', 'required']); !!}
                                @if ($errors->has('date'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('date') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>


                        <table class="table table-hover" id="datatable">
                            <thead>
                                <tr>
                                    <th>Employee ID.</th>
                                    <th>Employee Name</th>
                                    <th>Attendance</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                            @foreach($hrma as $employee)
                            <tr>
                                    <td>{{$employee->employee_id}}</td>
                                    <td>{{$employee->name}}</td>
                                    <td>
                                      <div class="form-group">
                                        <label class="radio-inline">
                                        <input type="radio" name="status-{{$employee->id}}" value="present"> Present</label>
                                        <label class="radio-inline">
                                        <input type="radio" name="status-{{$employee->id}}" value="absent"> Absent
                                        </label>
                                    </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="form-group">
                            <div class="col-md-4 col-md-offset-4">
                                <button type="submit" class="btn btn-primary btn-block">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link href="{{ asset('css/jquery.dataTables.min.css') }}" rel="stylesheet">
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {

    window.datatable = $('#datatable').DataTable( {
        data: [],
        columns: [
            { title: "Employee Id." },
            { title: "Employee Name" },
            { title: "Attendance" }
        ]
    });
    function load_data() {
        $.ajax({
            url: '/hrm_employees',
            dataType: 'json',
            headers: {'X-CSRF-TOKEN': window.Laravel.csrfToken },
            type: 'GET',
            data: filter_data,
            success: function(data) {
                compile_datatable(data);
            }
        });
    }

    function compile_datatable(students) {
        var compiled = [];
        // console.log('students', students);
        datatable.clear();
        $.each(students, function(index, value) {
            // console.log(compiled);
            datatable.row.add([
                value.email,
                value.first_name,
                value.last_name,
                '<div class="form-group">'+
                    '<label class="radio-inline">'+
                        '<input type="radio" name="'+value.id+'" value="1"> Present'+
                    '</label>'+
                    '<label class="radio-inline">'+
                        '<input type="radio" name="'+value.id+'" value="0"> Absent'+
                    '</label>'+
                '</div>'
            ]).draw();
        })
    }

});
</script>
  @stop
