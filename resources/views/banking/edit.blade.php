

<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('BankingController@update',[$bank_user->id]), 'method' => 'PUT', 'id' => 'bank_user_add_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Update User</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __( 'Name' ) . ':*') !!}
          {!! Form::text('name',$bank_user->name, ['class' => 'form-control', 'required', 'placeholder' => __( 'name' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('type_id', __( 'Type' ) . ':') !!}
        <select name="type_id" class="chosen-select-member form-control"  data-placeholder="Choose type...">
          @foreach($type as $data)
          <option value="{{ $data->id }}" 
              @if($data->id == $type_id[0]->type_id) 
                selected 
              @endif > {{ $data->name }} </option>
          @endforeach
          @foreach($type as $data)
            @if($data->deleted_at =="")     
              <option value="{{$data->id}}" > {{$data->name}}</option>
            @endif
            @endforeach  
          </select>
      </div>
      <div class="form-group">
        {!! Form::label('phone', __( 'Phone' ) . ':') !!}
          {!! Form::text('phone', $bank_user->phone, ['class' => 'form-control', 'placeholder' => __( 'phone' )]); !!}
      </div>
    
      <div class="form-group">
        {!! Form::label('account_no', __( 'Bank Account No' ) . ':') !!}
          {!! Form::text('account_no', $bank_user->account_no, ['class' => 'form-control', 'placeholder' => __( 'account_no' )]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->