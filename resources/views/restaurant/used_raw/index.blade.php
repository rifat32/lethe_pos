<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="panel panel-default">
            <div class="panel-heading">Raw Items Used to Cook this Dish</div>
                <div class="panel-body">
                        <div class="form-group">
                            <table id="myTable" class=" table order-list">
                                <thead>
                                    <tr>
                                        <td>Raw Item Name</td>
                                        <td>Quantity</td>
                                        <td>Price</td>
                                    </tr>
                                </thead>
                               <tbody>
                               <?php $sum=0; ?>
                                @foreach($raw_items as $raw_item)
                                <tr>
                                    <td style="text-align: left;">
                                       {{$raw_item->raw_item_name}}
                                    </td>
                                    <td  style="text-align: left;">
                                       {{$raw_item->used_quantity}} {{$raw_item->used_unit}}
                                    </td>
                                    <td  style="text-align: left;">
                                    ৳ {{$raw_item->used_quantity*($raw_item->unit_price/$raw_item->child_value)}}
                                       <?php 
                                       $sum+=$raw_item->used_quantity*($raw_item->unit_price/$raw_item->child_value);
                                       ?>
                                    </td>
                                </tr>
                                @endforeach
                               </tbody>
                               <tfoot>
                                    <tr>
                                    <td colspan="2" style="text-align: left;">
                                       <strong>Total</strong>
                                    </td>
                                    <td style="text-align: left;">
                                       <strong>৳ {{$sum}}</strong>
                                    </td>
                                    </tr>
                                </tfoot>
                            </table>  
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
                        </div>
                </div>
        </div>
    </div>
</div>
</div>