<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('HrmTransactionController@update', [$hrmt->id]), 'method' => 'PUT', 'id' => 'hrm_transactions_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Update HRM Transaction</h4>
    </div>

    <div class="modal-body">
     <div class="form-group">
        {!! Form::label('e_id', __( 'Employee ID' ) . ':*') !!}
          {!! Form::select('e_id', $hrm,$hrmt->e_id, ['class' => 'form-control', 'required', 'placeholder' => __( 'Employee ID' )]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('type', __( 'Type' ) . ':') !!}
          {!! Form::select('type', ['salary' => 'Salary', 'bonus' => 'Bonus', 'others' => 'Others'], $hrmt->type, ['class' => 'form-control', 'placeholder' => __( 'Type' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('amount', __( 'Amount' ) . ':') !!}
          {!! Form::text('amount', $hrmt->amount, ['class' => 'form-control', 'placeholder' => __( 'Amount' )]); !!}
      </div>
    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->