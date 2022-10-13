<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('UnitController@store'), 'method' => 'post', 'id' => $quick_add ? 'quick_add_unit_form' : 'unit_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'unit.add_unit' )</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('actual_name', __( 'unit.name' ) . ':*') !!}
          {!! Form::text('actual_name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'unit.name' )]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('short_name', __( 'unit.short_name' ) . ':*') !!}
          {!! Form::text('short_name', null, ['class' => 'form-control', 'placeholder' => __( 'unit.short_name' ), 'required']); !!}
      </div>

      <div class="form-group">
        {!! Form::label('allow_decimal', __( 'unit.allow_decimal' ) . ':*') !!}
          {!! Form::select('allow_decimal', ['1' => __('messages.yes'), '0' => __('messages.no')], null, ['placeholder' => __( 'messages.please_select' ), 'required', 'class' => 'form-control']); !!}
      </div>
      <div class="form-group">
        {!! Form::label('child_id', __( 'Parent Unit' )) !!}
        <select name="child_id" id="selectDynamic" class="chosen-select-member form-control"  data-placeholder="Choose user...">
        <option value="0" >None</option>
          @foreach($parent_units as $parent_unit)
          @if($parent_unit->deleted_at =="")   
              <option value="{{$parent_unit->id}}" > {{$parent_unit->actual_name}}</option>
          @endif
          @endforeach  
         
          </select>
      </div>
      <div class="form-group" id="Check" style="display:none";>
        {!! Form::label('child_value', __( '? Child = 1 Parent ' ) ) !!}
        {!! Form::text('child_value', null, ['class' => 'form-control', 'placeholder' => __( 'unit.name' )]); !!}
      </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
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