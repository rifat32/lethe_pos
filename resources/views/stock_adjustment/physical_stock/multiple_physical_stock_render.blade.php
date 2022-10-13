@php
    $sessionStatus = session()->has('physicalStockSession') ? session()->get('physicalStockSession')  : [];
    //$total = array_sum(array_column($cart,'total_price'));
    $i = 1;
@endphp
@foreach ($sessionStatus as $key => $item)
        <tr>
            <td># {{ $i++ }}</td>
            <td>{{ $item['sku'] }}</td>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['unit_price'] }}</td>
            <td>{{ $item['current_stock'] }}</td>
            <td> <span id="{{ $item['id'] }}">{{ $item['balance']??$item['balance'] }}</span></td>
            <td>
                <input name="physical_qty[]" type="text" class="forn-control physical_qty" data-stock=" {{ $item['current_stock'] }}"  data-id="{{ $item['id'] }}" value="{{ $item['physical_qty']??$item['physical_qty'] }}">
                <input type="hidden" name="product_id[]" value="{{ $item['id'] }}">
                <input type="hidden" name="current_stock[]" value="{{ $item['current_stock'] }}">
            </td>
            <td>
                <a class="btn btn-sm btn-danger remove" href="#" data-url="{{ action('StockAdjustmentController@multiProductPhysicalStockAjaxSessionSingelRemove') }}" data-id="{{ $item['id'] }}" >Remove</a>
            </td>
        </tr>
@endforeach
<input type="hiddent" id="count" value="{{ count($sessionStatus) }}">