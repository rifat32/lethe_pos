<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'ultimatePOS') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
                background-color: #ffffff;
                background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.12'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 3em;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .tagline{
                font-size:25px;
                font-weight: 300;
            }
            .background-img{
                /* background: lightblue url("{{asset('mainimg/images.jpeg')}}") no-repeat fixed center;  */
                background:  linear-gradient(rgba(0,0,0,0.527),rgba(0,0,0,0.5)), url(data:image/jpeg;base64,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)no-repeat center center fixed; 
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
                height: 100vh;
                width:100vw;
                display: flex;
                align-items: center;
                justify-content: center;
              color:#fafafa;
            }
        </style>
    </head>
    <body>




        <div class="flex-center position-ref full-height background-img">
            <div class="top-right links">

                @if (Route::has('login'))
                    @if (Auth::check())
                        <a style="color:#fafafa !important; " href="{{ action('HomeController@index') }}">@lang('home.home')</a>
                    @else
                        <a style="color:#fafafa !important; " href="{{ action('Auth\LoginController@login') }}">@lang('lang_v1.login')</a>
                        @if(env('ALLOW_REGISTRATION', true))
                            <!--<a href="{{ route('business.getRegister') }}">@lang('lang_v1.register')</a>-->
                        @endif
                    @endif
                @endif

                @if(Route::has('pricing') && config('app.env') != 'demo')
                    <a style="display:none" href="{{ action('\Modules\Superadmin\Http\Controllers\PricingController@index') }}">@lang('superadmin::lang.pricing')</a>
                @endif
            </div>

            <div class="content ">
     
                <div class="title m-b-md" style="font-weight: 600 !important">
                    {{ config('app.name', 'ultimatePOS') }}
                </div>
                <p class="tagline">
                    {{ env('APP_TITLE', '') }}
                </p>
            </div>
        </div>
    </body>
</html>
