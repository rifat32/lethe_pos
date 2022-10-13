<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
	<style media="all">
        @page {
			margin: 0;
			padding:0;
		}
		body{
			font-size: 0.875rem;
            font-family: '<?php echo  $font_family ?>';
            font-weight: bold;
            direction: <?php echo  $direction ?>;
            text-align: <?php echo  $text_align ?>;
			padding:0;
			margin:0;
		}
		.gry-color *,
		.gry-color{
			color:black;
		}
		table{
			width: 100%;
		}
		table th{
			font-weight: bold;
		}
		table.padding th{
			padding: .25rem .7rem;
		}
		table.padding td{
			padding: .25rem .7rem;
		}
		table.sm-padding td{
			padding: .1rem .7rem;
		}
		.border-bottom td,
		.border-bottom th{
			border-bottom:1px solid #eceff4;
		}
		.text-left{
			text-align:<?php echo  $text_align ?>;
		}
		.text-right{
			text-align:<?php echo  $not_text_align ?>;
		}
	</style>
</head>
<body>
	<div>

		@php
			$logo = "/img/" . \DB::table('logo')->first()->name;
		@endphp


		<div style="background: #eceff4;padding: 1rem;">
			<table>
				<tr>
					<td>
						@if($logo != null)
							<img src="{{ asset($logo) }}" height="30" style="display:inline-block;">
						@else
							<img src="{{ static_asset('assets/img/' . \DB::table('logo')->first()->name) }}" height="30" style="display:inline-block;">
						@endif
					</td>
					<td style="font-size: 1.5rem;" class="text-right strong">Invoice</td>
				</tr>
			</table>
			<table>
				<tr>
					<td style="font-size: 1rem;" class="strong">{{ env("APP_NAME") }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">{{ get_setting('contact_address') }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">Email: {{ get_setting('contact_email') }}</td>
					<td class="text-right small"><span class="gry-color small">Order Id:</span> <span class="strong">{{ $order->code }}</span></td>
				</tr>
				<tr>
					<td class="gry-color small">Phone: {{ get_setting('contact_phone') }}</td>
					<td class="text-right small"><span class="gry-color small">Order Date:</span> <span class=" strong">{{ date('d-m-Y', $order->date) }}</span></td>
				</tr>
			</table>

		</div>

		<div style="padding: 1rem;padding-bottom: 0">
            <table>
				@php
					$shipping_address = json_decode($order->shipping_address);
				@endphp
				<tr><td class="strong small gry-color">{{ ('Bill to') }}:</td></tr>
				<tr><td class="strong">{{ $shipping_address->name }}</td></tr>
                <tr><td class="strong small gry-color">{{ ('Delivery Man') }}:</td></tr>
				<tr><td class="strong">{{  $order->delivery_man }}</td></tr>

				<tr><td class="gry-color small">{{ $shipping_address->address }}, {{ $shipping_address->city }}, {{ $shipping_address->country }}</td></tr>
				<tr><td class="gry-color small">Email: {{ $shipping_address->email }}</td></tr>
				<tr><td class="gry-color small">Phone: {{ $shipping_address->phone }}</td></tr>
                <tr><td class="gry-color small">Alternative Phone Number: {{ $shipping_address->alternative_phone_number }}</td></tr>
			</table>
		</div>

	    <div style="padding: 1rem;">
			<table class="padding text-left small border-bottom">
				<thead>
	                <tr class="gry-color" style="background: #eceff4;">
	                    <th width="35%" class="text-left">Product Name</th>
						<th width="15%" class="text-left">Delivery Type</th>
	                    <th width="10%" class="text-left">Qty</th>
	                    <th width="15%" class="text-left">Unit Price</th>
	                    <th width="10%" class="text-left">Tax</th>
	                    <th width="15%" class="text-right">Total</th>
	                </tr>
				</thead>
				<tbody class="strong">
	                @foreach ($order->orderDetails as $key => $orderDetail)
		                @if ($orderDetail->product != null)
							<tr class="">
								<td>{{ $orderDetail->product->product->name }} @if($orderDetail->variation != null) ({{ $orderDetail->variation }}) @endif</td>
								<td>
									@if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
										{{ ('Home Delivery') }}
									@elseif ($orderDetail->shipping_type == 'pickup_point')
										@if ($orderDetail->pickup_point != null)
											{{ $orderDetail->pickup_point->name }} Pickip Point
										@endif
									@endif
								</td>
								<td class="">{{ $orderDetail->quantity }}</td>
								<td class="currency">৳{{ ($orderDetail->price/$orderDetail->quantity) }}</td>
								<td class="currency">৳{{ ($orderDetail->tax/$orderDetail->quantity) }}</td>
			                    <td class="text-right currency">৳{{ ($orderDetail->price+$orderDetail->tax) }}</td>
							</tr>
		                @endif
					@endforeach
	            </tbody>
			</table>
		</div>

	    <div style="padding:0 1.5rem;">
	        <table class="text-right sm-padding small strong">
	        	<thead>
	        		<tr>
	        			<th width="60%"></th>
	        			<th width="40%"></th>
	        		</tr>
	        	</thead>
		        <tbody>
			        <tr>
			            <td>
			            </td>
			            <td>
					        <table class="text-right sm-padding small strong">
						        <tbody>
							        <tr>
							            <th class="gry-color text-left">Sub Total</th>
							            <td class="currency">৳{{ ($order->orderDetails->sum('price')) }}</td>
							        </tr>
							        <tr>
							            <th class="gry-color text-left">{{ ('Shipping Cost') }}</th>
							            <td class="currency">৳{{ $order->shipping + $order->area_shipping }}</td>
							        </tr>
							        <tr class="border-bottom">
							            <th class="gry-color text-left">{{ ('Total Tax') }}</th>
							            <td class="currency">৳{{ ($order->orderDetails->sum('tax')) }}</td>
							        </tr>
				                    <tr class="border-bottom">
							            <th class="gry-color text-left">{{ ('Coupon Discount') }}</th>
							            <td class="currency">৳{{ ($order->coupon_discount) }}</td>
							        </tr>
                                    <tr class="border-bottom">
							            <th class="gry-color text-left">{{ ('Discount') }}</th>
							            <td class="currency">৳{{ ($order->discount) }}</td>
							        </tr>
							        <tr>
							            <th class="text-left strong">{{ ('Grand Total') }}</th>
							            <td class="currency">৳{{ ($order->orderDetails->sum('price') + $order->shipping + $order->area_shipping - $order->discount) }}</td>
							        </tr>
						        </tbody>
						    </table>
			            </td>
			        </tr>
		        </tbody>
		    </table>
	    </div>

	</div>
</body>
</html>
