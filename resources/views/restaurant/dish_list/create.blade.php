<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('Restaurant\DishListController@store'), 'method' => 'post', 'id' => 'dish_list_add_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Add Dish</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('dis_name', __( 'Dish Name' ) . ':*') !!}
          {!! Form::text('dis_name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'Dish Name' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('dish_category_id', __( 'Dish Category' ) . ':') !!}
        <select name="dish_category_id" class="chosen-select-member form-control"  data-placeholder="Choose Dish categoty...">
            @foreach($dish_category as $unit)
            @if($unit->deleted_at =="")         
                <option value="{{$unit->id}}" > {{$unit->dish_category_name}}</option>
            @endif
            @endforeach  
        </select>
      </div>
      <div class="form-group">
        {!! Form::label('dish_type', __( 'Dish Type' ) . ':') !!}
        <select name="dish_type" class="chosen-select-member form-control"  data-placeholder="Choose type...">
         
        <option value="Breakfast" >Breakfast</option>
        <option value="Lunch" >Lunch</option>
        <option value="Dinner" >Dinner</option>
  
        </select>
      </div>
      <div class="form-group">
        {!! Form::label('dish_price', __( 'Price' ) . ':') !!}
          {!! Form::text('dish_price', null, ['class' => 'form-control', 'placeholder' => __( 'Dish Price' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('dish_availability', __( 'Availability' ) . ':') !!}
          {!! Form::text('dish_availability', null, ['class' => 'form-control', 'placeholder' => __( 'Availability' )]); !!}
      </div>
     
</div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}
    
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->