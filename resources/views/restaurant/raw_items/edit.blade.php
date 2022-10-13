<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('Restaurant\InternalKitchenController@update', [$raw_items->id]), 'method' => 'PUT', 'id' => 'raw_item_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Edit Raw Item</h4>
    </div>

    <div class="modal-body">
    <div class="form-group">
        {!! Form::label('raw_item_name', __( 'Raw Item Name' ) . ':*') !!}
          {!! Form::text('raw_item_name', $raw_items->raw_item_name, ['class' => 'form-control', 'required', 'placeholder' => __( 'Raw Item Name' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('quantity', __( 'Quantity' ) . ':*') !!}
          {!! Form::text('quantity', $raw_items->quantity, ['class' => 'form-control', 'required', 'placeholder' => __( 'Raw Item Quantity' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('raw_item_unit', __( 'Raw Item Unit' ) . ':*') !!}
        <select name="raw_item_unit" class="chosen-select-member form-control"  data-placeholder="Choose unit...">
            @foreach($units as $unit)
         
              @if($unit->id == $raw_items->raw_item_unit)          
                <option value="{{$unit->id}}" selected > {{$unit->actual_name}}</option>
                @else
             <option value="{{$unit->id}}"  > {{$unit->actual_name}}</option>
              @endif
             
            @endforeach  
          
        </select>
      </div>
      <div class="form-group">
        {!! Form::label('raw_item_unit_price', __( 'Unit Price' ) . ':*') !!}
          {!! Form::text('raw_item_unit_price', $raw_items->raw_item_unit_price, ['class' => 'form-control', 'required', 'placeholder' => __( 'Unit Price' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('raw_item_used_unit', __( 'Used Unit' ) . ':') !!}
        <select name="raw_item_used_unit" class="chosen-select-member form-control"  data-placeholder="Choose used unit...">
        @foreach($units as $unit)
            
              @if($unit->id == $raw_items->raw_item_used_unit)          
                <option value="{{$unit->id}}" selected > {{$unit->actual_name}}</option>
             @else
             <option value="{{$unit->id}}"  > {{$unit->actual_name}}</option>
              @endif
            @endforeach  
          
        </select>
      </div>
      </div>
    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->