<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action('Restaurant\DishCategoryController@store'), 'method' => 'post', 'id' => 'dish_category_add_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Add Dish Category</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('dish_category_name', __( 'Dish Category' ) . ':*') !!}
          {!! Form::text('dish_category_name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'Dish Category Name' )]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->