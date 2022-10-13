
       <div class="container wrap">
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-md-offset-3">
            <div class="well well-sm">
                <div class="row">
                    <div class="col-sm-4 col-md-6 col-lg-7">
                        <img src="{{asset('storage/img/'.$expense_category->image)}}" alt="" class="img-rounded img-responsive" />
                    </div>
                    <div class="col-sm-8 col-md-6 col-lg-5">
                        <h4>{{$expense_category->name}}</h4>
                        <small><cite title="San Diego, USA">{{$expense_category->address}} <i class="glyphicon glyphicon-map-marker"></i></cite></small>
                        <p>
                            <i class="glyphicon glyphicon-envelope"></i>{{$expense_category->p_address}}
                            <br />
                            <i class="glyphicon glyphicon-globe"></i><a href="https://www.prepbootstrap.com">www.prepbootstrap.com</a>
                            <br />
                            <i class="glyphicon glyphicon-gift"></i>January 19, 1993
                        </p>
                        <div class="btn-group">
                            <a href="{{action('HrmController@edit', [$expense_category->id])}}" class="btn btn-primary"><span> Edit</span></a>
                            <a href="#" class="btn btn-danger"><span></span>Pay Salary</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .wrap
    {
        padding-top: 30px;
    }

    .glyphicon
    {
        margin-bottom: 10px;
        margin-right: 10px;
    }

    small
    {
        display: block;
        color: #888;
    }

    .well
    {
        border: 1px solid blue;
    }
</style>