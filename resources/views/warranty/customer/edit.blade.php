<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('CustomerWarrantyController@update', [$details->id]), 'method' => 'PUT', 'id' => 'customer_warranty_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Edit Customer Warranty</h4>
    </div>

    <div class="modal-body">
     <div class="form-group">
        {!! Form::label('customer', ( 'Customer' ) . ':*') !!}
        <select name="customer_name"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
          <option value="{{$customer_warranty->customer_name}}" selected>{{$customer_warranty->customer_name}}</option>
            @foreach($customer as $item)
          <option value="{{$item->name}}">{{$item->name}}</option>
          @endforeach
          </select>
      </div>

      <div class="form-group">
        {!! Form::label('product', ( 'Product' ) . ':*') !!}
        <select name="product_name"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
          <option value="{{$customer_warranty->product_name}}" selected>{{$customer_warranty->product_name}}</option>
            @foreach($product as $item2)
          <option value="{{$item2->name}}">{{$item2->name}}</option>
          @endforeach
          </select>
      </div>

      <div class="form-group">
        {!! Form::label('reason', ( 'Reason' ) . ':') !!}
         <input type="text" class="form-control" name="reason" value="{{$customer_warranty->reason}}">
      </div>
      <div class="form-group">
        {!! Form::label('status', ( 'Status' ) . ':') !!}
         <select name="status"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
         <option value="{{$customer_warranty->status}}" selected>{{$customer_warranty->status}}</option>
          <option value="Pending">Pending</option>
          <option value="Progress">Inprogress</option>
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