

<!-- if something wring. i put some variable out of condition fix that -->
<div id="preview_body">
	@php
		$loop_count = 0;
	@endphp
	
			
	
	@foreach($product_details as $details)
	
		@while($details['qty'] > 0)
			@php
				$loop_count += 1;
				$is_new_row = true;
			@endphp
				{{-- Paper Internal --}}
			
	
	
			<div
			class="printHeight"
				style="
				
				width:75% !important; 
				display:block;
				padding:auto auto;
				margin:auto auto;
				 padding: 2rem 0 !important;
				
				" class="sticker-border text-center"
			
			>
		
			<div
				style="
				display:block;
				padding:auto auto;
				margin:auto auto;
				text-align:center
				
				
				"
			>
			    
			
		
	
			
					<span  style="display: block !important;font-size:1.2rem!important;">
						<b>
						   Name</b>:{{$details['details']->product_name}}
					</span>
	
				{{-- Price --}}
			
				<span style="display: block !important;font-size:1.2rem!important;  font-weight:bold;">
				    {{--
				    	@if(!empty($print['price']))
				<b>Price:</b>
				<span class="display_currency" data-currency_symbol = true>
					@if($print['price_type'] == 'inclusive')
						{{$details['details']->sell_price_inc_tax}}
					@else
						{{$details['details']->default_sell_price}}
					@endif
				</span>
			@endif
				    
				    
				    --}}
				    	<b>Price:</b>
				<span class="display_currency" data-currency_symbol = true>
				
						{{$details['details']->default_sell_price}}
		
				</span>
				
			
				</span>
				
	
	
				{{-- Barcode --}}
				  <img 
				
				style="display:block; margin:0 auto!important; height:auto; width:auto;"
				src="data:image/png;base64,{{DNS1D::getBarcodePNG($details['details']->product_id, 'C128', 1.5,53,array(39, 48, 54), true)}}"> 
	
	</div>
			</div>

			@php
			$details['qty'] = $details['qty'] - 1;
		@endphp
	@endwhile
@endforeach

		
	
		
	
		
	

	
	</div>
	
	<style type="text/css">
	
		@media print{
		   	#preview_body{
				display: block !important;
			}
		     body {
    margin-top: -2rem !important;
    
  }
		   
			.printHeight{
			    min-height:100vh !important;
			    height:100vh !important;
			    max-height:100vh !important;
			    display:block;
			    padding: 2rem 0 !important;
			}
		}
	
		/*@page {*/
		/*	size: 2.00 in 1.50in ;*/
		/*	margin-top: 0in;*/
		/*	margin-bottom: 0in;*/
		/*	margin-left: 0in;*/
		/*	margin-right: 0in;*/
			
		
		/*}*/
	
	</style>