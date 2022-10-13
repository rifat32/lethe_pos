<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="panel panel-default">
            <div class="panel-heading">Add Raw Items Used to Cook this Dish</div>
                <div class="panel-body">
                    <form method="post" action="{{route('storeusedRaws')}}">
                        <div class="form-group">
                        {{ csrf_field() }}
                            <!-- <input name="_token" required="" class="form-control" value="s1k3QJcFJjUwnjHvI050RI3Ahmaj0f3YH0tUdIiK" type="hidden"> -->
                            <input name="dish_id" required="" class="form-control" value="{{$id}}" type="hidden">
                            <table id="myTable" class=" table order-list">
                                <thead>
                                    <tr>
                                        <td>Raw Item Name</td>
                                        <td>Quantity</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="col-sm-2">
                                            <select name="raw_item_id" class="chosen-select-member form-control"  data-placeholder="Choose Raw Item...">
                                                @foreach($raw_items as $item)
                                                @if($item->deleted_at =="")         
                                                    <option value="{{$item->id}}" > {{$item->raw_item_name}}</option>
                                                @endif
                                                @endforeach  
                                            </select>
                                        </td>
                                        <td class="col-sm-2">
                                            <input name="used_quantity" class="form-control" required="" type="text">
                                        </td>
                                        <td class="col-sm-2">
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                    <td colspan="5" style="text-align: left;">
                                        <input class="btn btn-lg btn-primary " id="addrow" value="New" type="button">
                                    </td>
                                    </tr>
                                </tfoot>
                            </table>  
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
                        </div>
                    </form>
                </div>
                <script>
                    $(document).ready(function () {
                        var counter = 0;
                        $("#addrow").on("click", function () {
                            var newRow = $("<tr>");
                            var cols = "";
                            cols += " <td class='col-sm-2'><select name='raw_item_id' class='chosen-select-member form-control'  data-placeholder='Choose Dish categoty...'>@foreach($raw_items as $item)@if($item->deleted_at =="")         <option value='{{$item->id}}' > {{$item->raw_item_name}}</option> @endif @endforeach  </select></td>";
                            cols += '<td><input type="text" class="form-control" name="used_quantity" required/></td>';
                            cols += '<td><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Delete"></td>';
                            newRow.append(cols);
                            $("table.order-list").append(newRow);
                            counter++;
                        });
                        $("table.order-list").on("click", ".ibtnDel", function (event) {
                            $(this).closest("tr").remove();       
                            counter -= 1
                        });
                    });
                    // function calculateRow(row) {
                    //     var price = +row.find('input[name^="price"]').val();

                    // }

                    // function calculateGrandTotal() {
                    //     var grandTotal = 0;
                    //     $("table.order-list").find('input[name^="price"]').each(function () {
                    //         grandTotal += +$(this).val();
                    //     });
                    //     $("#grandtotal").text(grandTotal.toFixed(2));
                    // }
            </script>
        </div>
    </div>
</div>