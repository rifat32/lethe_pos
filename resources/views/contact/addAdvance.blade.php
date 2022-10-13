<div class="modal-dialog modal-md" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('TransactionPaymentController@storeAdvance',[$contact_id]), 'method' => 'PUT', 'id' => 'quick_add_contact' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Add Advance Balance</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
              {!! Form::label('balance', __('Advance Balance') . ':') !!}
              <div class="input-group">
                  <span class="input-group-addon">
                      <i class="fa fa-money"></i>
                  </span>
                  {!! Form::text('balance', 0, ['class' => 'form-control input_number']); !!}
              </div>
          </div>
        </div>
    </div>
    </div>
    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}
  
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->