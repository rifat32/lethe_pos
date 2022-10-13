<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="panel panel-default">
            <div class="panel-heading">Add Product</div>
                <div class="panel-body">
                    <form method="post" action="{{route('storeProduct')}}">
                        <div class="form-group">
                        {{ csrf_field() }}
                            <!-- <input name="_token" required="" class="form-control" value="s1k3QJcFJjUwnjHvI050RI3Ahmaj0f3YH0tUdIiK" type="hidden"> -->
                            <input name="agent_id" required="" class="form-control" value="{{$id}}" type="hidden">
                            
                            <table id="myTable" class=" table order-list">
                                <thead>
                                    <tr>
                                        <td>Product</td>
                                        <td>Quantity</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="col-sm-2">
                                            <select name="product_id" class="chosen-select-member form-control"  data-placeholder="Select Product...">
                                                @foreach($products as $item)
                                           
                                                    <option value="{{$item->product_id}}-{{$item->variations_id}}" > {{$item->product_name}}{{$item->sub_sku}}</option>
                                            
                                                @endforeach  
                                            </select>
                                        </td>
                                      
                                        <td class="col-sm-2">
                                            <input name="product_quantity" class="form-control" required="" type="text">
                                        </td>
                                        <td class="col-sm-2">
                                        </td>
                                    </tr>
                                </tbody>
                                
                            </table>  
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
                        </div>
                    </form>
                </div>
        </div>
    </div>
</div>