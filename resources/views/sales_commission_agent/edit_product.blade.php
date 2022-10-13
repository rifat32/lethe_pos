
<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('SalesCommissionAgentController@updateProduct', [$id]), 'method' => 'PUT', 'id' => 'sale_commission_agent_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Update Stock</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-md-0">
        <div class="form-group">
            <input name="agent_id" class="form-control" value="{{$edit_products->agent_id}}" type="hidden">
        </div>
      </div>
      <div class="col-md-5">
        <div class="form-group">
          {!! Form::label('product_id','Product'. ':*') !!}
          <select name="product_id" class="chosen-select-member form-control"  data-placeholder="Select Product...">
            @foreach($products as $item)
            @if($edit_products->product_id === $item->product_id)           
                <option value="{{$item->product_id}}" selected> {{$item->product_name}}</option>
            @else
                <option value="{{$item->product_id}}" > {{$item->product_name}}</option>
            @endif
          
            @endforeach  
        </select>
        </div>
      </div>
      <div class="col-md-5">
        <div class="form-group">
          {!! Form::label('product_quantity', 'Product Quantity' . ':') !!}
            {!! Form::text('product_quantity', $edit_products->product_quantity, ['class' => 'form-control', 'placeholder' => __( 'Product Quantity' ) ]); !!}
            
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