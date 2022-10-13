@extends('layouts.app')
@section('title','Product Download')

@section('content')
<section class="content-header">
    <h1>Product Download Reports</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <a class="btn btn-primary btn-sm" onclick="exportF(this)">Excel Download</a>
                    
                    <a class="btn btn-info btn-sm" id="cmd">Pdf Download</a>
                </div>
                <div class="box-body" id="data">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table" border="1" onclick="demoFromHTML()">
                         <colgroup>
                            <col width="20%">
                            <col width="20%">
                            <col width="20%">
                            <col width="20%">
                            <col width="20%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>@lang('business.product')</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Purchase Price</th>
                                <th>Excellent Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>{{$product->product}}</td>
                                <td>{{$product->category}}</td>
                                <td>{{$product->qty_available}}</td>
                                <td>{{$product->default_purchase_price}}</td>
                                <td>{{$product->sell_price_inc_tax}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <p>{{$products->render()}}</p>
        </div>
    </div>

    
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
   <script>
       function exportF(elem) {
          var table = document.getElementById("table");
          var html = table.outerHTML;
          var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
          elem.setAttribute("href", url);
          elem.setAttribute("download", "export.xls"); // Choose the file name
          return false;
        }
        
     $(document).on('click','#cmd',function(){
         
         var pdf = new jsPDF('p', 'pt', 'letter');
            // source can be HTML-formatted string, or a reference
            // to an actual DOM element from which the text will be scraped.
            source = $('#table')[0];

            // we support special element handlers. Register them with jQuery-style 
            // ID selector for either ID or node name. ("#iAmID", "div", "span" etc.)
            // There is no support for any other type of selectors 
            // (class, of compound) at this time.
            specialElementHandlers = {
                // element with id of "bypass" - jQuery style selector
                '#bypassme': function(element, renderer) {
                    // true = "handled elsewhere, bypass text extraction"
                    return true
                }
            };
            margins = {
                top: 80,
                bottom: 60,
                left: 40,
                width: 522
            };
            // all coords and widths are in jsPDF instance's declared units
            // 'inches' in this case
            pdf.fromHTML(
                    source, // HTML string or DOM elem ref.
                    margins.left, // x coord
                    margins.top, {// y coord
                        'width': margins.width, // max width of content on PDF
                        'elementHandlers': specialElementHandlers
                    },
            function(dispose) {
                // dispose: object with X, Y of the last line add to the PDF 
                //          this allow the insertion of new lines after html
                pdf.save('Test.pdf');
            }
            , margins);
     })
   </script>
@endsection