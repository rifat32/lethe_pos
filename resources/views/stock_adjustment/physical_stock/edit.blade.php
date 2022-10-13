<div class="modal-dialog modal-lg" role="document">
    <form method="post" action="{{action('StockAdjustmentController@PhysicalStockupdate',$item->id)}}">
        
        {{csrf_field()}}
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">update Physical Stock of product :{{$item->products->name}}, sku: {{$item->products->sku}}</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="form-group">
            <label for="formGroupExampleInput">Current Stock</label>
            <input type="text" class="form-control" value="{{$item->current_stock}}" readonly name="current_stock" id="current_stock">
          </div>
          <div class="form-group">
            <label for="formGroupExampleInput2">Physical Stock</label>
            <input type="text" class="form-control" value="{{$item->physical_qty}}" name="physical_qty" required id="physical_qty">
          </div>
          
          <div class="form-group">
            <label for="formGroupExampleInput2">Balance</label>
            <input type="text" class="form-control" value="{{$item->physical_qty- $item->current_stock}}" readonly id="balance">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </div>
    </form>
  </div>