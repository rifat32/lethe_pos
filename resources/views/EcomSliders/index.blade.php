
@extends('layouts.app')
<style>


    /* Set a style for all buttons */
    .modalbtn {
      background-color: #04AA6D;
      color: white;
      padding: 14px 20px;
      margin: 8px 0;
      border: none;
      cursor: pointer;
      width: 100%;
      opacity: 0.9;
    }

    .modalbtn:hover {
      opacity:1;
    }

    /* Float cancel and delete buttons and add an equal width */
    .cancelbtn, .deletebtn {
      float: left;
      width: 50%;
    }

    /* Add a color to the cancel button */
    .cancelbtn {
      background-color: #ccc;
      color: black;
    }

    /* Add a color to the delete button */
    .deletebtn {
      background-color: #f44336;
    }

    /* Add padding and center-align text to the container */
    .container2 {
      padding: 16px;
      text-align: center;
    }

    /* The Modal (background) */
    .modal2 {
      display: none; /* Hidden by default */
      position: fixed; /* Stay in place */
      z-index: 1; /* Sit on top */
      left: 0;
      top: 0;
      width: 100%; /* Full width */
      height: 100%; /* Full height */
      overflow: auto; /* Enable scroll if needed */
      background-color: #474e5d;
      padding-top: 50px;
    }

    /* Modal Content/Box */
    .modal-content2 {
      background-color: #fefefe;
      margin: 5% auto 15% auto; /* 5% from the top, 15% from the bottom and centered */
      border: 1px solid #888;
      width: 80%; /* Could be more or less, depending on screen size */
    }

    /* Style the horizontal ruler */
    hr {
      border: 1px solid #f1f1f1;
      margin-bottom: 25px;
    }

    /* The Modal Close Button (x) */
    .close2 {
      position: absolute;
      right: 35px;
      top: 15px;
      font-size: 40px;
      font-weight: bold;
      color: #f1f1f1;
    }

    .close2:hover,
    .close2:focus {
      color: #f44336;
      cursor: pointer;
    }

    /* Clear floats */
    .clearfix::after {
      content: "";
      clear: both;
      display: table;
    }

    /* Change styles for cancel button and delete button on extra small screens */
    @media screen and (max-width: 300px) {
      .cancelbtn, .deletebtn {
         width: 100%;
      }
    }
    </style>
@section('title', "slider")



@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{ ('All uploaded files')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('uploaded-files.create') }}" class="btn btn-primary">
				<span>{{ ('Upload New File')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card content container" >
    <form id="sort_uploads" action="">
        <div class="card-header row gutters-5">
            <div class="col-md-3">
                <h5 class="mb-0 h6">{{ ('All files')}}</h5>
            </div>
            <div class="col-md-3 ml-auto mr-0">
                <select class="form-control form-control-xs aiz-selectpicker" name="sort" onchange="sort_uploads()">
                    <option value="newest" @if($sort_by == 'newest') selected="" @endif>{{  ('Sort by newest') }}</option>
                    <option value="oldest" @if($sort_by == 'oldest') selected="" @endif>{{  ('Sort by oldest') }}</option>
                    <option value="smallest" @if($sort_by == 'smallest') selected="" @endif>{{  ('Sort by smallest') }}</option>
                    <option value="largest" @if($sort_by == 'largest') selected="" @endif>{{  ('Sort by largest') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control form-control-xs" name="search" placeholder="{{  ('Search your files') }}" value="{{ $search }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">{{  ('Search') }}</button>
            </div>
        </div>
    </form>
    <div class="card-body">
    	<div class="row gutters-5">
    		@foreach($all_uploads as $key => $file)
    			@php
    				if($file->file_original_name == null){
    				    $file_name =  ('Unknown');
    				}else{
    					$file_name = $file->file_original_name;
	    			}
    		@endphp

                <div></div>
    			<div class="col-md-3 w-140px w-lg-220px">
    				<div class="aiz-file-box">
    					{{-- <div class="dropdown-file" >
    						<a class="dropdown-link" data-toggle="dropdown">
    							<i class="fa fa-ellipsis-v"></i>
    						</a>
    						<div class="dropdown-menu dropdown-menu-right">
    							<a href="javascript:void(0)" class="dropdown-item" onclick="detailsInfo(this)" data-id="{{ $file->id }}">
    								<i class="fa fa-info-circle mr-2"></i>
    								<span>{{  ('Details Info') }}</span>
    							</a>
    							<a href="{{ asset($file->file_name) }}" target="_blank" download="{{ $file_name }}.{{ $file->extension }}" class="dropdown-item">
    								<i class="fa fa-download mr-2"></i>
    								<span>{{  ('Download') }}</span>
    							</a>
    							<a href="javascript:void(0)" class="dropdown-item" onclick="copyUrl(this)" data-url="{{ asset($file->file_name) }}">
    								<i class="fa fa-clipboard mr-2"></i>
    								<span>{{  ('Copy Link') }}</span>
    							</a>
    							<a href="javascript:void(0)" class="dropdown-item confirm-alert" data-href="{{ route('uploaded-files.destroy', $file->id ) }}" data-target="#delete-modal">
    								<i class="fa fa-trash mr-2"></i>
    								<span>{{  ('Delete') }}</span>
    							</a>
    						</div>
    					</div> --}}
    					<div class="card card-file aiz-uploader-select c-default" title="{{ $file_name }}.{{ $file->extension }}">
    						<div class="card-file-thumb">

    							@if($file->type == 'image')
    								<img src="{{ asset($file->file_name) }}" class="img-fit" height="200" width="200">

    							@elseif($file->type == 'video')
    								<i class="fa fa-file-video"></i>
    							@else
    								<i class="fa fa-file"></i>
    							@endif
    						</div>

    						<div class="card-body">
                                <a class="btn btn-danger text-center mb-1"  href="{{route('uploaded-files.destroy',$file->id)}}">
                                    Delete
                                </a>
    						</div>
    					</div>
    				</div>
    			</div>
    		@endforeach
    	</div>
		<div class="aiz-pagination mt-3">
			{{ $all_uploads->appends(request()->input())->links() }}
		</div>
    </div>
</div>
@endsection


<div id="id01" class="modal2">
    <span onclick="document.getElementById('id01').style.display='none'" class="close2" title="Close Modal">×</span>
    <form class="modal-content2" id="submit_delete" method="POST" >
        {!! method_field('delete') !!}
        {!! csrf_field() !!}
      <div class="container2">
        <h1>Delete Account</h1>
        <p>Are you sure you want to delete your account?</p>

        <div class="clearfix">
          <button type="button" onclick="document.getElementById('id01').style.display='none'" class="modalbtn cancelbtn">Cancel</button>
          <button type="submit" onclick="document.getElementById('id01').style.display='none'" class="modalbtn deletebtn">Delete</button>
        </div>
      </div>
    </form>
  </div>

<script>
   function func(link){
    document.getElementById('id01').style.display='block';
    document.getElementById('submit_delete').setAttribute("action", link)

    console.log(link)

   }
// Get the modal
var modal = document.getElementById('id01');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>


@section('script')
	<script type="text/javascript">
		function detailsInfo(e){
            $('#info-modal-content').html('<div class="c-preloader text-center absolute-center"><i class="fa fa-spinner la-spin la-3x opacity-70"></i></div>');
			var id = $(e).data('id')
			$('#info-modal').modal('show');
			$.post('{{ route('uploaded-files.info') }}', {_token: AIZ.data.csrf, id:id}, function(data){
                $('#info-modal-content').html(data);
				// console.log(data);
			});
		}
		function copyUrl(e) {
			var url = $(e).data('url');
			var $temp = $("<input>");
		    $("body").append($temp);
		    $temp.val(url).select();
		    try {
			    document.execCommand("copy");
			    AIZ.plugins.notify('success', '{{  ('Link copied to clipboard') }}');
			} catch (err) {
			    AIZ.plugins.notify('danger', '{{  ('Oops, unable to copy') }}');
			}
		    $temp.remove();
		}
        function sort_uploads(el){
            $('#sort_uploads').submit();
        }
	</script>
@endsection
