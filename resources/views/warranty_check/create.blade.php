<head>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />

</head>
<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('WarrantyCheckController@store'), 'method' => 'post', 'id' => 'warranty_check_add_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Add Customer Warranty</h4>
    </div>


    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('invoice_id', ( 'Invoice ID' ) . ':*') !!}
        </br>
        <select name="invoice_id" id="invoice_id" style="width:200px;">
         <option></option>
            @foreach($invoice as $invoices)
          <option value="{{$invoices->id}}">{{$invoices->id}}</option>
          @endforeach
          </select>
      </div>
      
      <div class="form-group">
        {!! Form::label('Warranty_issued_date', ( 'Warranty Issued Date' ) . ':') !!}
        
          {!! Form::date('warranty_issued_date', null, ['class' => 'form-control', 'placeholder' => ( '' )]); !!}
      </div>
      
      <div class="form-group">
        {!! Form::label('duration', ( 'Duration' ) . ':') !!}
          {!! Form::text('duration', null, ['class' => 'form-control', 'placeholder' => ( 'Duration' )]); !!}
      </div>
     
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
 
<script src="{{('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js')}}"></script>

  <script type="text/javascript">
  $("#invoice_id").select2({
      placeholder:'Select Invoice Id',
      Editable:true,
      allowClear:true
  });
  </script>
</div><!-- /.modal-dialog -->