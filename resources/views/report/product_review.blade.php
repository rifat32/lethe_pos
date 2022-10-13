@extends('layouts.app')
@section('title','Product Review')

@section('content')
<section class="content-header">
    <h1>Product Review Reports</h1>
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
                    <table class="table table-bordered table-striped text-center" id="table" border="1" onclick="demoFromHTML()">
                         <colgroup>
                            <col width="20%">
                            <col width="20%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>@lang('business.product')</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>{{$product->product}}</td>
                                <td>{{$product->category}}</td>
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
        
    $(document).ready(function() {

            $("#cmd").click(function() {
                var pdf = new jsPDF('p', 'pt', 'letter');

    pdf.cellInitialize();
    pdf.setFontSize(10);
    $.each( $('tr'), function (i, row){
        $.each( $(row).find("td, th"), function(j, cell){
            var txt = $(cell).text().trim() || " ";
            var width = (j==4) ? 150 : 150; //make 4th column smaller
            pdf.cell(10, 50, width, 30, txt, i);
        });
    });

    pdf.save('sample-file.pdf');

        });
        
    });
   </script>
@endsection