
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

    #drop-area {
  border: 2px dashed #ccc;
  border-radius: 20px;
  width: 480px;
  font-family: sans-serif;
  margin: 100px auto;
  padding: 20px;
}
#drop-area.highlight {
  border-color: purple;
}
p {
  margin-top: 0;
}
.my-form {
  margin-bottom: 10px;
}
#gallery {
  margin-top: 10px;
}
#gallery img {
  width: 150px;
  margin-bottom: 10px;
  margin-right: 10px;
  vertical-align: middle;
}
.button {
  display: inline-block;
  padding: 10px;
  background: #ccc;
  cursor: pointer;
  border-radius: 5px;
  border: 1px solid #ccc;
}
.button:hover {
  background: #ddd;
}
#fileElem {
  display: none;
}

    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.0/min/dropzone.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.0/dropzone.js"></script>
@section('title', "slider")



@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{ ('Upload New File')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('slider.view') }}" class="btn btn-link text-reset">
				<i class="las la-angle-left"></i>
				<span>{{ ('Back to uploaded files')}}</span>
			</a>
		</div>
	</div>
</div>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ ('Drag & drop your files')}}</h5>
    </div>
    <div class="card-body">

        <form method="post" action="{{ route('uploaded-files.store') }}" enctype="multipart/form-data"
        class="dropzone" id="dropzone">
        {{ csrf_field() }}

      </form>
      <div class="row mt-3">
        <div class="col-lg-12 margin-tb">
            <div class="text-center">
                <a class="btn btn-success" href="{{ route('slider.view') }}" title="return to index"> <i class="fa fa-backward fa-2x"></i>
                </a>
            </div>
        </div>
    </div>




    </div>

</div>
@endsection





<script>
      <script type="text/javascript">
        Dropzone.options.dropzone =
        {
            maxFilesize: 12,
            resizeQuality: 1.0,
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            addRemoveLinks: true,
            timeout: 60000,
            removedfile: function(file)
            {
                var name = file.upload.filename;
                $.ajax({
                    headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                    type: 'POST',
                    url: '{{ url("uploaded-files.destroy") }}',
                    data: {filename: name},
                    success: function (data){
                        console.log("File has been successfully removed!!");
                    },
                    error: function(e) {
                        console.log(e);
                    }});
                    var fileRef;
                    return (fileRef = file.previewElement) != null ?
                    fileRef.parentNode.removeChild(file.previewElement) : void 0;
            },
            success: function (file, response) {
                console.log(response);
            },
            error: function (file, response) {
                return false;
            }
        };
    </script>
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



<script>

// let dropArea = document.getElementById('drop-area')

// dropArea.addEventListener('dragenter', handlerFunction, false)
// dropArea.addEventListener('dragleave', handlerFunction, false)
// dropArea.addEventListener('dragover', handlerFunction, false)
// dropArea.addEventListener('drop', handlerFunction, false)

// let dropArea = document.getElementById('drop-area');

// ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
//   dropArea.addEventListener(eventName, preventDefaults, false)
// })

// function preventDefaults (e) {
//   e.preventDefault()
//   e.stopPropagation()
// }
// ;['dragenter', 'dragover'].forEach(eventName => {
//   dropArea.addEventListener(eventName, highlight, false)
// })

// ;['dragleave', 'drop'].forEach(eventName => {
//   dropArea.addEventListener(eventName, unhighlight, false)
// })

// function highlight(e) {
//   dropArea.classList.add('highlight')
// }

// function unhighlight(e) {
//   dropArea.classList.remove('highlight')
// }
// dropArea.addEventListener('drop', handleDrop, false)

// function handleDrop(e) {
//   let dt = e.dataTransfer
//   let files = dt.files

//   handleFiles(files)
// }

</script>

