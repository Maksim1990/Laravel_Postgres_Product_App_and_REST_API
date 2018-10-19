@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Import</div>

                    <form style="border: 4px solid #a1a1a1;margin-top: 15px;padding: 10px;"
                          action="{{ route("import_file",['type'=>$type]) }}" class="form-horizontal"
                          method="post" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <input type="hidden" id="type" name="type" value="{{$type}}"/>
                        <input type="file" id="file1" name="file"/>
                        <button class="btn btn-primary" id="import_button" disabled>Import</button>
                    </form>
                    <div class="col-sm-10 col-sm-offset-1 w3-center" id="file_name" style="height: 60px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $("#file1").change(function () {

            var val = $(this).val();

            switch (val.substring(val.lastIndexOf('.') + 1).toLowerCase()) {
                case 'csv':
                    $('#file_name').html('File ' + val + ' is chosen');
                    $('#file_name').css('color', 'green');
                    $('#import_button').prop('disabled', false);
                    break;
                default:
                    $('#file_name').html('Format is invalid!');
                    $('#file_name').css('color', 'red');
                    $('#import_button').prop('disabled', true);
                    break;
            }
        });
    </script>
@endsection