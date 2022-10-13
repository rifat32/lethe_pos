<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('CustomerWarrantyController@store'), 'method' => 'post', 'id' => 'customer_warranty_add_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Add Customer Warranty</h4>
    </div>


    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('customer', ( 'Customer' ) . ':*') !!}
        <select name="customer_name"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
            @foreach($customer as $item)
          <option value="{{$item->name}}">{{$item->name}}</option>
          @endforeach
          </select>
      </div>

      <div class="form-group">
        {!! Form::label('product', ('Product') . ':') !!}
        <select name="product_name"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
            @foreach($product as $item2)
          <option value="{{$item2->name}}">{{$item2->name}}</option>
          @endforeach
          </select>
      </div>
      
      <div class="form-group">
        {!! Form::label('reason', ( 'Reason' ) . ':') !!}
          {!! Form::text('reason', null, ['class' => 'form-control', 'placeholder' => ( 'Reason' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('status', __( 'Status' ) . ':') !!}
          <select name="status"  class="chosen-select-member form-control"  data-placeholder="Choose type...">
          <option value="Pending">Pending</option>
          <option value="Progress">Inprogress</option>
          <option value="Processed">Processed</option>
          </select>
      </div>
    </div>
  <div class="form-group" style="width: 90%; margin: 0 auto">
      <label for="start_date">Start Date</label>
      <input type="date" class="form-control" name="start_date" id="start_date">
    </div>
    <div class="form-group" style="width: 90%; margin: 0 auto">
      <label for="end_date">End Date</label>
      <input type="date" class="form-control" name="end_date" id="end_date">
    </div>
    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->