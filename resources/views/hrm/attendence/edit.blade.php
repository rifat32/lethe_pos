<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('HrmAttendenceController@update', [$hrma->id]), 'method' => 'PUT', 'id' => 'hrm_attendence_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Update Attendance/h4>
    </div>

    <div class="modal-body">
     <div class="form-group">
        {!! Form::label('e_id', __( 'Employee ID' ) . ':*') !!}
          {!! Form::select('e_id', $hrm, $hrma->e_id, ['class' => 'form-control', 'required', 'placeholder' => __( 'Employee ID' )]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('date', __( 'Date' ) . ':*') !!}
      <div class="form-group">
          {!! Form::date('date', $hrma->date, ['class' => 'form-control', 'placeholder' => __( 'Date' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('status', __( 'Status' ) . ':*') !!}
      <div class="form-group">
          <!-- {!! Form::text('status', $hrma->status, ['class' => 'form-control', 'placeholder' => __( 'Status' )]); !!} -->
          {!! Form::select('status', ['present' => 'present', 'absent' => 'absent'], $hrma->status, ['class' => 'form-control','required', 'placeholder' => __( 'Status' )]); !!}
      </div>
    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->