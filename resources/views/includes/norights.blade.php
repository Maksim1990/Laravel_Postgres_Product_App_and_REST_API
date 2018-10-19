<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Meet Mate App</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <!-- Styles -->
        <style>

            html, body {
                background-repeat: no-repeat;
                background-size: 100% 100%;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
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
            .content {
                text-align: center;
            }
            .title {
                font-size: 84px;
            }
            a {
                color: black;
                padding: 0 25px;
                font-size: 55px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }
          
            .m-b-md {
                margin-top: 10px;
            }
            .warning{font-size: 35px;}
            b{font-size: 45px;}
        </style>

    </head>
    <body>

                            <div class="w3-row s12 m12 l12 w3-center m-b-md">
                                <p class='warning'><b>Ooops!</b><br>
                                Seems like your session is expired or you don't have rights to see this content<br>
                                Please, try to login again</p>
                                <img style="margin-top: -15px;"  height="200" src="http://www.heaven4netent.com/wp-content/uploads/2015/10/Sorry.jpg" alt="">
                                @if (Route::has('login'))
                        
                                        <p class='login'><a href="{{ url('/login') }}">Login</a></p>
                                    
                                @endif
                                <p style="font-size: 30px;">
                                    Developed by Maksim Narushevich
                                </p>
                            </div>


    </body>
</html>
