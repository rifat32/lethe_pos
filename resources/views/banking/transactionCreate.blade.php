<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('BankTransactionController@store'), 'method' => 'post', 'id' => 'bank_transaction_add_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Add Transaction</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('u_id', __( 'User Name' ) . ':*') !!}
        <select name="u_id" class="chosen-select-member form-control"  data-placeholder="Choose user...">
                            @foreach($data as $users)
                            @if($users->deleted_at =="")   
                                <option value="{{$users->id}}" > {{$users->name}}</option>
                                @endif
                                @endforeach  
                            </select>
      </div>

      <div class="form-group">
        {!! Form::label('type', __( 'Type' ) . ':') !!}
        <select name="type" class="chosen-select-member form-control"  data-placeholder="Choose type...">
                           
                           <option value="Transfered" >Transfered</option>
                           <option value="Received" >Received</option>
                       </select>
    

    </div>
    <div class="form-group">
        {!! Form::label('balance', __( 'Balance' ) . ':') !!}
          {!! Form::text('balance', null, ['class' => 'form-control', 'placeholder' => __( 'balance' )]); !!}
      </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->