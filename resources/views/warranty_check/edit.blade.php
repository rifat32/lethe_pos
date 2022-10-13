@extends('layouts.app')
@section('title', __('Check Warranty'))

@section('content')


<head>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />

</head>
<div class="modal-dialog" role="document">
  <div class="modal-content">
    <form action="{{url('update_warranty',$details->id)}}" method="post" id="warranty_check_add_form">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
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
         <option value="{{$details->invoice_id}}">{{$details->invoice_id}}</option>
            @foreach($invoice as $invoices)
          <option value="{{$invoices->id}}" selected>{{$invoices->id}}</option>
          @endforeach
          </select>
      </div>
      
      <div class="form-group">
        {!! Form::label('Warranty_issued_date', ( 'Warranty Issued Date' ) . ':') !!}
        
          {!! Form::date('warranty_issued_date', null, ['class' => 'form-control', 'placeholder' => ( 'Reason' )]); !!}
      </div>
      
      <div class="form-group">
        {!! Form::label('duration', ( 'Duration' ) . ':') !!}
         <input type="text" name="duration" class="form-control" value="{{$details->duration}}"/>
      </div>
     
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

  </form>

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
@endsection