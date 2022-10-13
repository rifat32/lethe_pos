<div class="modal-dialog" role="document">
  <div class="modal-content">
  <script>
$(document).ready( function() {
	$('#e_id').change(function(){
		//console.log($(this).val());
		var val=$(this).val();
		if( val==""){
			$('#result').hide();
		}
		else{
			$('#result').show();
      $("#result").html(val);
      }
	});
});


</script>

    {!! Form::open(['url' => action('HrmTransactionController@store'), 'method' => 'post', 'id' => 'hrm_transactions_add_form' ]) !!}
    <div class="modal-header">
  
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Add Banking Category</h4>
    </div>
    
    <div class="modal-body">
  <div id="result" style="display:none";></div>
      <div class="form-group">
        {!! Form::label('e_id', __( 'Employee ID' ) . ':*') !!}
          {!! Form::select('e_id',$hrmt, null, ['class' => 'form-control', 'required', 'placeholder' => __( 'Employee ID' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('salary', __( 'Salary' ) . ':*') !!}
        <div id="result" style="display:none";></div>
      </div>
      <div class="form-group">
        {!! Form::label('type', __( 'Type' ) . ':') !!}
          {!! Form::select('type', ['salary' => 'Salary', 'bonus' => 'Bonus', 'others' => 'Others'],null, ['class' => 'form-control','required', 'placeholder' => __( 'Type' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('amount', __( 'Amount' ) . ':') !!}
          {!! Form::text('amount', null, ['class' => 'form-control','required', 'placeholder' => __( 'Amount' )]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

 
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

