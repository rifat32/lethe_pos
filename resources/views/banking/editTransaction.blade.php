<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('BankTransactionController@update',[$bank_transaction->id]), 'method' => 'PUT', 'id' => 'bank_transaction_add_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Update Transaction</h4>
    </div>
    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('u_id', __( 'User Name' ) . ':*') !!}
        <select name="u_id" class="chosen-select-member form-control"  data-placeholder="Choose user...">
                        @foreach($data as $users)
                            @if($users->deleted_at =="")
                                @if($bank_transaction->u_id==$users->id)   
                                    <option value="{{$users->id}}" selected> {{$users->name}}</option>
                                @else
                                <option value="{{$users->id}}"> {{$users->name}}</option>
                                @endif
                            @endif
                        @endforeach  
                            </select>
      </div>
      <div class="form-group">
        {!! Form::label('type', __( 'Type' ) . ':') !!}
        <select name="type" class="chosen-select-member form-control"  data-placeholder="Choose type...">
            @if($bank_transaction->type=="Transfered")   
            <option value="Transfered" selected>Transfered</option>
            <option value="Received" >Received</option>
            @else
            <option value="Transfered" >Transfered</option>
            <option value="Received" selected>Received</option>
            @endif
        </select>
    </div>
    <div class="form-group">
        {!! Form::label('balance', __( 'Balance' ) . ':') !!}
          {!! Form::text('balance', $bank_transaction->balance, ['class' => 'form-control', 'placeholder' => __( 'balance' )]); !!}
      </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>
    {!! Form::close() !!}
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->