<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('TransferBalancePersonalController@update', [$tb_personal->id]), 'method' => 'PUT', 'id' => 'transfer_balance_personal_add_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Transfer Balance To Other Source</h4>
     
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('receiver', __( 'expense.receiver' ) . ':*') !!}
          {!! Form::text('receiver', $tb_personal->receiver, ['class' => 'form-control', 'required', 'placeholder' => __( 'expense.receiver' )]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('phone', __( 'expense.phone' ) . ':') !!}
          {!! Form::text('phone', $tb_personal->phone, ['class' => 'form-control', 'placeholder' => __( 'expense.phone' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('amount', __( 'expense.amount' ) . ':') !!}
          {!! Form::text('amount', $tb_personal->amount, ['class' => 'form-control', 'placeholder' => __( 'expense.amount' )]); !!}
      </div>
    
      <div class="form-group">
        {!! Form::label('reason', __( 'expense.reason' ) . ':*') !!}
          {!! Form::text('reason', $tb_personal->reason, ['class' => 'form-control','required', 'placeholder' => __( 'expense.reason' )]); !!}
      </div>

      <div class="form-group">
      <input type="hidden" name="sender" id="sender" value={{auth()->user()->id}}>
      </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->