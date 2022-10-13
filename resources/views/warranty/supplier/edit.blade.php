<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('SupplierWarrantyController@update', [$supplier_warranty->id]), 'method' => 'PUT', 'id' => 'supplier_warranty_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Edit Supplier Warranty</h4>
    </div>

    <div class="modal-body">
    <div class="form-group">
        {!! Form::label('warrent_id', ( 'Warrent ID' ) . ':*') !!}
        <select name="warrent_id"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
          <option value="{{$supplier_warranty->warrent_id}}" selected>{{$supplier_warranty->warrent_id}}</option>
            @foreach($warrent as $item1)
          <option value="{{$item1->id}}">{{$item1->id}}</option>
          @endforeach
          </select>
      </div>

     <div class="form-group">
        {!! Form::label('customer', ( 'Customer' ) . ':*') !!}
        <select name="customer_name"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
          <option value="{{$supplier_warranty->customer_name}}" selected>{{$supplier_warranty->customer_name}}</option>
            @foreach($warrent as $item)
          <option value="{{$item->customer_name}}">{{$item->customer_name}}</option>
          @endforeach
          </select>
      </div>

      <div class="form-group">
        {!! Form::label('product', ( 'Product' ) . ':*') !!}
        <select name="product_name"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
          <option value="{{$supplier_warranty->product_name}}" selected>{{$supplier_warranty->product_name}}</option>
            @foreach($warrent as $item2)
          <option value="{{$item2->product_name}}">{{$item2->product_name}}</option>
          @endforeach
          </select>
      </div>

      <div class="form-group">
        {!! Form::label('reason', ( 'Reason' ) . ':') !!}
         <input type="text" class="form-control" name="reason" value="{{$supplier_warranty->reason}}">
      </div>
      <div class="form-group">
        {!! Form::label('status', ( 'Status' ) . ':') !!}
         <select name="status"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
         <option value="{{$supplier_warranty->status}}" selected>{{$supplier_warranty->status}}</option>
         
          <option value="Inprogress">Inprogress</option>
          <option value="Processed">Processed</option>
          </select>
      </div>
     
    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->