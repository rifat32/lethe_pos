<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('SupplierWarrantyController@store'), 'method' => 'post', 'id' => 'supplier_warranty_add_form' ]) !!}
    <div class="modal-body">

    <div class="form-group">
        {!! Form::label('warrent_id', ( 'Warrent Id' ) . ':*') !!}
        <select name="warrent_id"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
            @foreach($warrent as $item1)
          <option value="{{$item1->id}}">{{$item1->id}}</option>
          @endforeach
          </select>
      </div>

      <div class="form-group">
        {!! Form::label('customer', ( 'Customer' ) . ':*') !!}
        <select name="customer_name"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
            @foreach($warrent as $item)
          <option value="{{$item->customer_name}}">{{$item->customer_name}}</option>
          @endforeach
          </select>
      </div>

      <div class="form-group">
        {!! Form::label('product', ('Product') . ':') !!}
        <select name="product_name"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
            @foreach($warrent as $item2)
          <option value="{{$item2->product_name}}">{{$item2->product_name}}</option>
          @endforeach
          </select>
      </div>
      
      <div class="form-group">
        {!! Form::label('reason', ( 'Reason' ) . ':') !!}
          {!! Form::text('reason', null, ['class' => 'form-control', 'placeholder' => ( 'Reason' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('status', ( 'Status' ) . ':') !!}
          <select name="status"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
         
          <option value="Inprogress">Inprogress</option>
          <option value="Processed">Processed</option>
          </select>
      </div>
    </div>
    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->