<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('UnitController@update', [$unit->id]), 'method' => 'PUT', 'id' => 'unit_edit_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'unit.edit_unit' )</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('actual_name', __( 'unit.name' ) . ':*') !!}
          {!! Form::text('actual_name', $unit->actual_name, ['class' => 'form-control', 'required', 'placeholder' => __( 'unit.name' )]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('short_name', __( 'unit.short_name' ) . ':*') !!}
          {!! Form::text('short_name', $unit->short_name, ['class' => 'form-control', 'placeholder' => __( 'unit.short_name' ), 'required']); !!}
      </div>

      <div class="form-group">
        {!! Form::label('allow_decimal', __( 'unit.allow_decimal' ) . ':*') !!}
          {!! Form::select('allow_decimal', ['1' => __('messages.yes'), '0' => __('messages.no')], $unit->allow_decimal, ['placeholder' => __( 'messages.please_select' ), 'required', 'class' => 'form-control']); !!}
      </div>
      <div class="form-group">
        {!! Form::label('child_id', __( 'Parent Unit' )) !!}
        <select name="child_id" id="selectDynamic" class="chosen-select-member form-control"  placeholder="Choose Child Unit...">
            @foreach($parent_units as $parent_unit)
            @if($parent_unit->deleted_at =="")
            @if($parent_unit->id == $unit->child_id)
                <option value="{{$parent_unit->id}}" selected> {{$parent_unit->actual_name}}</option>
            @else
            <option value="{{$parent_unit->id}}" > {{$parent_unit->actual_name}}</option>
            @endif
            @endif
            @endforeach
            <option value="0" >None</option>  
          </select>
      </div>
      <div class="form-group"  id='Check' style="display:none";>
        {!! Form::label('child_value', __( '? Child = 1 Parent ' ) ) !!}
        {!! Form::text('child_value', $unit->child_value, ['class' => 'form-control', 'placeholder' => __( 'unit.name' )]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
$(document).ready( function() {
	$('#selectDynamic').change(function(){
		//console.log($(this).val());
		var bal=$(this).val();
		if( bal=="0"){
			$('#Check').hide();
		}
		else{ 
			$('#Check').show();

		}
	});
});
</script>