<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="panel panel-default">
            <div class="panel-heading">Sub Categories of {{$parent_name->name}}:</div>
            <div class="form-group">
                            <table id="myTable" class=" table order-list">
                                <thead>
                                    <tr>
                                        <td>Category</td>
                                        <td>Category Code</td>
                                    </tr>
                                </thead>
                               <tbody>
                                @foreach($sub_categories as $sub_category)
                                <tr>
                                    <td style="text-align: left;">
                                       {{$sub_category->name}}
                                    </td>
                                    <td colspan="5" style="text-align: left;">
                                    @if($sub_category->code=="")
                                        NULL
                                    @else
                                       {{$sub_category->code}}
                                    @endif
                                    </td>
                                </tr>
                                @endforeach
                               </tbody>
                               
                            </table>  
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
                        </div>
                <!-- <div class="panel-body">
                    <table>
                        <thead>
                            <th>Category</th>
                            <th>Category Code</th>
                        </thead>
                        <tbody>
                        @foreach($sub_categories as $sub_category)
                        @if($sub_category->delted_at=="")
                            <tr>
                                <td>
                                    {{$sub_category->name}}
                                </td>
                                <td>
                                    {{$sub_category->name}}
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div> -->
            </div>
        </div>
    </div>
</div>
